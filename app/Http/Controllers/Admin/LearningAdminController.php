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
}
