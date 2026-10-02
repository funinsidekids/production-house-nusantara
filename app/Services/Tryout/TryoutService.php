<?php

namespace App\Services\Tryout;

use App\Models\AnswerAttempt;
use App\Models\Question;
use App\Models\Tryout;
use App\Models\TryoutAnswer;
use App\Models\TryoutSession;
use Illuminate\Support\Facades\DB;

/**
 * Tryout lifecycle: start -> autosave answers (idempotent) -> submit (idempotent scoring).
 */
class TryoutService
{
    /**
     * Start (or resume) a session for the given user & tryout.
     * Reuses an existing active session so retries from Android are safe.
     */
    public function start(Tryout $tryout, int $userId): TryoutSession
    {
        if (! in_array($tryout->status, ['open', 'scheduled'], true)) {
            throw new \RuntimeException('Tryout belum dibuka.');
        }

        $existing = TryoutSession::where('tryout_id', $tryout->id)
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->latest('started_at')
            ->first();

        if ($existing) {
            return $existing;
        }

        $questionIds = $this->orderedQuestionIds($tryout);

        return TryoutSession::create([
            'tryout_id' => $tryout->id,
            'user_id' => $userId,
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'status' => 'active',
            'question_order' => $questionIds,
            'flagged_questions' => [],
            'started_at' => now(),
            'expires_at' => now()->addMinutes(max(1, (int) $tryout->duration_minutes)),
        ]);
    }

    /** @return array<int, int> */
    private function orderedQuestionIds(Tryout $tryout): array
    {
        if (! empty($tryout->question_order)) {
            return array_values($tryout->question_order);
        }

        $ids = $tryout->items()->pluck('question_id')->all();

        if ($ids === [] && $tryout->question_set_id) {
            $ids = DB::table('question_set_questions')
                ->where('question_set_id', $tryout->question_set_id)
                ->orderBy('position')
                ->pluck('question_id')
                ->all();
        }

        shuffle($ids);

        return $ids;
    }

    /**
     * Idempotent answer upsert. Duplicate requests (same client_request_id or
     * same session+question) never create duplicate rows.
     *
     * @param  array{question_id:int, selected_option_key?:string|null, answer_text?:string|null, time_spent_seconds?:int, flagged?:bool, client_request_id?:string|null}  $data
     */
    public function saveAnswer(TryoutSession $session, array $data): TryoutAnswer
    {
        if ($session->status !== 'active') {
            throw new \RuntimeException('Sesi sudah ditutup.');
        }

        $question = Question::findOrFail($data['question_id']);

        // Enforce that the question belongs to this session's order.
        $order = $session->question_order ?? [];
        if ($order !== [] && ! in_array($question->id, array_map('intval', $order), true)) {
            throw new \RuntimeException('Soal tidak termasuk dalam sesi ini.');
        }

        // Idempotency by client_request_id: return the previously stored row untouched.
        if (! empty($data['client_request_id'])) {
            $prior = TryoutAnswer::where('client_request_id', $data['client_request_id'])->first();
            if ($prior) {
                return $prior;
            }
        }

        $isCorrect = null;
        if ($question->type === 'multiple_choice' || $question->type === 'true_false') {
            $correctKey = $question->options->where('is_correct', true)->first()?->option_key
                ?? $question->answer_key;
            $isCorrect = isset($data['selected_option_key']) && $correctKey !== null
                ? mb_strtoupper($data['selected_option_key']) === mb_strtoupper($correctKey)
                : false;
        } elseif ($question->answer_key !== null && ! empty($data['answer_text'])) {
            $isCorrect = Question::normalizeText($data['answer_text'])
                === Question::normalizeText($question->answer_key);
        }

        $answer = TryoutAnswer::updateOrCreate(
            [
                'tryout_session_id' => $session->id,
                'question_id' => $question->id,
            ],
            [
                'selected_option_key' => $data['selected_option_key'] ?? null,
                'answer_text' => $data['answer_text'] ?? null,
                'is_correct' => $isCorrect,
                'time_spent_seconds' => (int) ($data['time_spent_seconds'] ?? 0),
                'flagged' => (bool) ($data['flagged'] ?? false),
                'attempt_number' => DB::raw('attempt_number + 1'),
                'client_request_id' => $data['client_request_id'] ?? null,
            ],
        );

        $session->forceFill([
            'answered_count' => $session->answers()->count(),
            'flagged_questions' => $session->answers()->where('flagged', true)->pluck('question_id'),
        ])->save();

        return $answer->refresh();
    }

    /**
     * Submit is idempotent: once a session has a submit_idempotency_key and is
     * completed, repeat calls return the locked result unchanged.
     */
    public function submit(TryoutSession $session, string $idempotencyKey): TryoutSession
    {
        if ($session->status === 'completed') {
            return $session; // previous result is final; never recompute.
        }

        if ($session->status !== 'active') {
            throw new \RuntimeException('Sesi tidak dapat disubmit.');
        }

        DB::transaction(function () use ($session, $idempotencyKey) {
            // Lock the row so two parallel submits cannot both score.
            $locked = TryoutSession::whereKey($session->id)->lockForUpdate()->first();

            if ($locked->status === 'completed') {
                $session->refresh();

                return;
            }

            $tryout = $locked->tryout;
            $answers = $locked->answers()->with('question.options')->get();

            $totalPoints = 0.0;
            $earnedPoints = 0.0;
            $bySubject = [];
            $byTopic = [];
            $correctCount = 0;

            foreach ($answers as $answer) {
                $q = $answer->question;
                $points = (float) DB::table('tryout_questions')
                    ->where('tryout_id', $tryout->id)
                    ->where('question_id', $q->id)
                    ->value('points') ?? 1;

                $totalPoints += $points;

                // Re-evaluate correctness server-side at submit time.
                $isCorrect = $this->evaluate($q, $answer);
                $answer->forceFill(['is_correct' => $isCorrect])->save();

                if ($isCorrect) {
                    $earnedPoints += $points;
                    $correctCount++;
                }

                $bySubject[$q->subject_id] ??= ['total' => 0, 'correct' => 0];
                $bySubject[$q->subject_id]['total']++;
                $bySubject[$q->subject_id]['correct'] += $isCorrect ? 1 : 0;

                if ($q->topic_id) {
                    $byTopic[$q->topic_id] ??= ['total' => 0, 'correct' => 0];
                    $byTopic[$q->topic_id]['total']++;
                    $byTopic[$q->topic_id]['correct'] += $isCorrect ? 1 : 0;
                }

                // Mirror into append-only answer_attempts (skip if already synced).
                AnswerAttempt::firstOrCreate(
                    [
                        'user_id' => $locked->user_id,
                        'question_id' => $q->id,
                        'tryout_session_id' => $locked->id,
                    ],
                    [
                        'topic_id' => $q->topic_id,
                        'selected_option_key' => $answer->selected_option_key,
                        'answer_text' => $answer->answer_text,
                        'is_correct' => $isCorrect,
                        'time_spent_seconds' => $answer->time_spent_seconds,
                        'source' => $tryout->isTka() ? 'tka' : 'tryout',
                        'answered_at' => now(),
                    ],
                );
            }

            $questionTotal = max(1, count($locked->question_order ?? []));
            $score = $totalPoints > 0 ? round($earnedPoints / $totalPoints * 100, 2) : 0;
            $accuracy = $questionTotal > 0 ? round($correctCount / $questionTotal * 100, 2) : 0;
            $avgTime = $answers->count() > 0
                ? (int) round($answers->avg('time_spent_seconds'))
                : 0;

            $locked->forceFill([
                'status' => 'completed',
                'submitted_at' => now(),
                'duration_seconds' => $locked->started_at->diffInSeconds(now()),
                'score' => $score,
                'accuracy' => $accuracy,
                'submit_idempotency_key' => $idempotencyKey,
                'statistics' => [
                    'answered' => $answers->count(),
                    'correct' => $correctCount,
                    'unanswered' => max(0, $questionTotal - $answers->count()),
                    'avg_time_seconds' => $avgTime,
                    'by_subject' => $bySubject,
                    'by_topic' => $byTopic,
                ],
            ])->save();

            $session->setRawAttributes($locked->getAttributes(), true);
        });

        // Progress update happens outside the lock transaction (safe/idempotent aggregation).
        app(\App\Services\Progress\ProgressService::class)->rebuildForUser($session->user_id);

        return $session;
    }

    private function evaluate(Question $question, TryoutAnswer $answer): bool
    {
        if ($question->type === 'essay' || $question->type === 'short_answer') {
            // Essay/short answer without a key are unscored (null) — keep prior decision.
            if ($question->answer_key === null) {
                return (bool) $answer->is_correct;
            }

            return $answer->answer_text !== null
                && Question::normalizeText($answer->answer_text) === Question::normalizeText($question->answer_key);
        }

        $correctKey = $question->options->where('is_correct', true)->first()?->option_key
            ?? $question->answer_key;

        return $answer->selected_option_key !== null
            && $correctKey !== null
            && mb_strtoupper($answer->selected_option_key) === mb_strtoupper($correctKey);
    }
}
