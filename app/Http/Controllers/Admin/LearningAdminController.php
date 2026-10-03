<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LearningAdminController extends Controller
{
    public function subjects(): View
    {
        $subjects = DB::table('subjects')->orderBy('sort_order')->orderBy('name')->get();

        return view('admin-learn.subjects', compact('subjects'));
    }

    public function storeSubject(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:20', Rule::unique('subjects', 'code')],
            'group' => ['required', Rule::in(['umum', 'peminatan', 'keagamaan', 'muatan_lokal'])],
        ]);
        DB::table('subjects')->insert($data + [
            'active' => true, 'sort_order' => 99,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return back()->with('status', 'Mapel ditambahkan.');
    }

    public function materials(): View
    {
        $materials = DB::table('materials')
            ->leftJoin('subjects', 'subjects.id', '=', 'materials.subject_id')
            ->orderByDesc('materials.updated_at')
            ->limit(200)
            ->get(['materials.id', 'materials.title', 'materials.type', 'materials.status', 'materials.grade', 'materials.updated_at', 'subjects.name as subject_name']);

        $subjects = DB::table('subjects')->where('active', true)->orderBy('name')->get(['id', 'name']);

        return view('admin-learn.materials', compact('materials', 'subjects'));
    }

    public function questions(): View
    {
        $questions = DB::table('questions')
            ->leftJoin('subjects', 'subjects.id', '=', 'questions.subject_id')
            ->orderByDesc('questions.updated_at')
            ->limit(200)
            ->get(['questions.id', 'questions.question_text', 'questions.type', 'questions.difficulty', 'questions.status', 'questions.updated_at', 'subjects.name as subject_name']);

        return view('admin-learn.questions', compact('questions'));
    }

    public function tryouts(): View
    {
        $tryouts = DB::table('tryouts')
            ->leftJoin('subjects', 'subjects.id', '=', 'tryouts.subject_id')
            ->orderByDesc('tryouts.created_at')
            ->limit(200)
            ->get(['tryouts.id', 'tryouts.title', 'tryouts.type', 'tryouts.status', 'tryouts.duration_minutes', 'tryouts.question_count', 'subjects.name as subject_name']);

        return view('admin-learn.tryouts', compact('tryouts'));
    }

    public function updateTryoutStatus(Request $request, int $tryout): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(['draft', 'scheduled', 'open', 'closed', 'archived'])]]);
        $affected = DB::table('tryouts')->where('id', $tryout)->update($data + ['updated_at' => now()]);

        return $affected ? back()->with('status', 'Status tryout diperbarui.') : back()->withErrors(['tryout' => 'Tryout tidak ditemukan.']);
    }

    public function aiQuestions(): View
    {
        $rows = DB::table('ai_generated_questions')
            ->orderByDesc('created_at')
            ->limit(200)
            ->get();

        return view('admin-learn.ai-questions', compact('rows'));
    }

    public function students(): View
    {
        $students = DB::table('users')
            ->whereRaw("LOWER(COALESCE(primary_role,'')) NOT IN ('superadmin','admin')")
            ->orderByDesc('created_at')
            ->limit(300)
            ->get(['id', 'name', 'email', 'username', 'status', 'approval_status', 'created_at']);

        return view('admin-learn.students', compact('students'));
    }

    public function updateQuestionStatus(Request $request, int $question): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(['draft', 'pending', 'validated', 'published', 'rejected', 'archived'])]]);
        $affected = DB::table('questions')->where('id', $question)->update($data + ['updated_at' => now()]);

        return $affected ? back()->with('status', 'Status soal diperbarui.') : back()->withErrors(['question' => 'Soal tidak ditemukan.']);
    }

    public function reviewAiQuestion(Request $request, int $id, string $decision): RedirectResponse
    {
        abort_unless(in_array($decision, ['approve', 'reject'], true), 404);
        $row = DB::table('ai_generated_questions')->where('id', $id)->first();
        if (! $row) {
            return back()->withErrors(['ai' => 'Data AI question tidak ditemukan.']);
        }

        if ($decision === 'approve') {
            // Publish: jadikan soal resmi (source ai_generated, status published).
            $q = $row->question_json ?? null;
            DB::table('ai_generated_questions')->where('id', $id)->update([
                'review_status' => 'approved',
                'status' => 'published',
                'reviewed_by' => optional($request->user())->id,
                'reviewed_at' => now(),
                'updated_at' => now(),
            ]);
            if (! $row->question_id) {
                $payload = json_decode((string) $q, true);
                if (is_array($payload) && ! empty($payload['question'])) {
                    $newId = DB::table('questions')->insertGetId([
                        'subject_id' => $row->subject_id ?: null,
                        'topic_id' => $row->topic_id ?: null,
                        'grade' => $row->grade ?: 'XII',
                        'type' => $row->type ?: 'multiple_choice',
                        'difficulty' => $row->difficulty ?: 'medium',
                        'question_text' => mb_substr((string) $payload['question'], 0, 6000),
                        'explanation' => mb_substr((string) ($payload['explanation'] ?? ''), 0, 6000) ?: null,
                        'source' => 'ai_generated',
                        'status' => 'published',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    foreach (($payload['options'] ?? []) as $i => $opt) {
                        DB::table('question_options')->insert([
                            'question_id' => $newId,
                            'option_key' => $opt['key'] ?? chr(65 + $i),
                            'option_text' => (string) ($opt['text'] ?? ''),
                            'is_correct' => strtoupper((string) ($payload['correct_answer'] ?? '')) === strtoupper($opt['key'] ?? chr(65 + $i)),
                            'position' => $i + 1,
                            'created_at' => now(), 'updated_at' => now(),
                        ]);
                    }
                    DB::table('ai_generated_questions')->where('id', $id)->update(['question_id' => $newId]);
                }
            }

            return back()->with('status', 'Soal AI disetujui & dipublikasikan.');
        }

        DB::table('ai_generated_questions')->where('id', $id)->update([
            'review_status' => 'rejected',
            'status' => 'rejected',
            'reviewed_by' => optional($request->user())->id,
            'reviewed_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('status', 'Soal AI ditolak.');
    }
}
