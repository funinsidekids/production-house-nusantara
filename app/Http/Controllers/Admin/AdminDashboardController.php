<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $nonStaff = "LOWER(COALESCE(primary_role,\'\')) NOT IN (\'superadmin\',\'admin\')";

        $stats = [
            'students' => DB::table('users')->whereRaw($nonStaff)->count(),
            'active_students' => DB::table('users')->where('status', 'active')->whereRaw($nonStaff)->count(),
            'subjects' => DB::table('subjects')->where('active', true)->count(),
            'materials' => DB::table('materials')->where('status', 'published')->count(),
            'questions' => DB::table('questions')->where('status', 'published')->count(),
            'draft_questions' => DB::table('questions')->where('status', 'draft')->count(),
            'tryouts' => DB::table('tryouts')->whereIn('status', ['open', 'scheduled'])->count(),
            'sessions' => DB::table('tryout_sessions')->count(),
            'ai_generated' => DB::table('ai_generated_questions')->count(),
            'ai_pending' => DB::table('ai_generated_questions')->where('review_status', 'pending')->count(),
        ];

        $recentSessions = DB::table('tryout_sessions')
            ->join('tryouts', 'tryouts.id', '=', 'tryout_sessions.tryout_id')
            ->join('users', 'users.id', '=', 'tryout_sessions.user_id')
            ->orderByDesc('tryout_sessions.started_at')
            ->limit(8)
            ->get(['tryout_sessions.id', 'tryouts.title as tryout_title', 'users.name as user_name', 'tryout_sessions.status', 'tryout_sessions.score', 'tryout_sessions.started_at']);

        $topStudents = DB::table('answer_attempts')
            ->join('users', 'users.id', '=', 'answer_attempts.user_id')
            ->selectRaw('users.id, users.name, COUNT(*) total, SUM(answer_attempts.is_correct) correct')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn ($r) => (object) [
                'name' => $r->name,
                'total' => (int) $r->total,
                'accuracy' => $r->total > 0 ? round(($r->correct / $r->total) * 100) : 0,
            ]);

        return view('admin-learn.dashboard', compact('stats', 'recentSessions', 'topStudents'));
    }
}
