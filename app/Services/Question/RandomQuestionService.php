<?php

namespace App\Services\Question;

use App\Models\Question;
use Illuminate\Support\Collection;

/**
 * Random question engine.
 *
 * Pulls questions with SQL-level filtering (never loads the whole table),
 * avoids duplicates via normalized content hash, and shuffles server-side.
 */
class RandomQuestionService
{
    /**
     * @param  array{subject_id?:int, chapter_id?:int, topic_id?:int, grade?:string, difficulty?:string, type?:string, exclude_ids?:array<int>}  $filters
     * @return Collection<int, Question>
     */
    public function pick(array $filters, int $count): Collection
    {
        $count = max(1, min($count, (int) config('learning.max_random_questions')));

        // Difficulty "mixed" -> take an even spread across easy/medium/hard.
        if (($filters['difficulty'] ?? 'mixed') === 'mixed') {
            return $this->pickMixed($filters, $count);
        }

        return $this->query($filters)->inRandomOrder()->limit($count)->get();
    }

    private function pickMixed(array $filters, int $count): Collection
    {
        $levels = ['easy', 'medium', 'hard'];
        $per = intdiv($count, count($levels));
        $result = collect();

        foreach ($levels as $level) {
            $f = array_merge($filters, ['difficulty' => $level]);
            $result = $result->merge($this->query($f)->inRandomOrder()->limit($per)->get());
        }

        // Fill remainder from any difficulty, excluding already-picked ids & hashes.
        $remaining = $count - $result->count();
        if ($remaining > 0) {
            $f = array_merge($filters, [
                'exclude_ids' => $result->pluck('id')->all(),
                'exclude_hashes' => $result->pluck('content_hash')->filter()->all(),
            ]);
            unset($f['difficulty']);
            $result = $result->merge($this->query($f)->inRandomOrder()->limit($remaining)->get());
        }

        return $result->shuffle()->values()->take($count);
    }

    private function query(array $filters): \Illuminate\Database\Eloquent\Builder
    {
        $q = Question::query()
            ->with('options')
            ->where('status', 'published');

        foreach (['subject_id', 'chapter_id', 'topic_id', 'curriculum_id'] as $col) {
            if (! empty($filters[$col])) {
                $q->where($col, $filters[$col]);
            }
        }

        if (! empty($filters['grade'])) {
            $q->where('grade', $filters['grade']);
        }

        if (! empty($filters['type'])) {
            $q->where('type', $filters['type']);
        }

        if (! empty($filters['difficulty']) && $filters['difficulty'] !== 'mixed') {
            $q->where('difficulty', $filters['difficulty']);
        }

        if (! empty($filters['exclude_ids'])) {
            $q->whereNotIn('id', (array) $filters['exclude_ids']);
        }

        if (! empty($filters['exclude_hashes'])) {
            $q->whereNotIn('content_hash', (array) $filters['exclude_hashes']);
        }

        return $q;
    }
}
