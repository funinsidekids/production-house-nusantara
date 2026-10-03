<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SuperadminDashboardController extends Controller
{
    public function index(): View
    {
        $pending = User::query()
            ->whereRaw("LOWER(COALESCE(primary_role,'')) <> 'superadmin'")
            ->where('approval_status', User::APPROVAL_PENDING)
            ->orderBy('created_at')
            ->limit(8)
            ->get();

        $stats = [
            'users' => User::query()->whereRaw("LOWER(COALESCE(primary_role,'')) <> 'superadmin'")->count(),
            'admins' => User::query()->whereRaw("LOWER(COALESCE(primary_role,'')) = 'admin'")->count(),
            'pending' => User::query()->whereRaw("LOWER(COALESCE(primary_role,'')) <> 'superadmin'")->where('approval_status', User::APPROVAL_PENDING)->count(),
            'subjects' => DB::table('subjects')->count(),
            'questions' => DB::table('questions')->count(),
            'tryouts' => DB::table('tryouts')->count(),
            'materials' => DB::table('materials')->count(),
            'sessions' => DB::table('tryout_sessions')->count(),
        ];

        $audit = DB::table('audit_logs')->orderByDesc('id')->limit(10)->get();

        return view('superadmin.dashboard', compact('stats', 'pending', 'audit'));
    }
}
