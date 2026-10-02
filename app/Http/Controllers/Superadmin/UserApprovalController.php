<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Superadmin: satu-satunya yang bisa menambah / menyetujui akun User & Admin.
 * Akun superadmin TIDAK PERNAH ditampilkan di daftar manapun.
 */
class UserApprovalController extends Controller
{
    private function baseQuery()
    {
        return User::query()->whereRaw("LOWER(COALESCE(primary_role,'')) <> 'superadmin'");
    }

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q'));
        $status = $request->query('approval_status');

        $users = $this->baseQuery()
            ->when($q !== '', fn ($qq) => $qq->where(function ($w) use ($q) {
                $w->where('name', 'like', "%$q%")
                    ->orWhere('email', 'like', "%$q%")
                    ->orWhere('username', 'like', "%$q%");
            }))
            ->when(in_array($status, [User::APPROVAL_PENDING, User::APPROVAL_APPROVED, User::APPROVAL_REJECTED], true),
                fn ($qq) => $qq->where('approval_status', $status))
            ->orderByRaw("CASE approval_status WHEN 'pending' THEN 0 ELSE 1 END")
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'total' => $this->baseQuery()->count(),
            'pending' => $this->baseQuery()->where('approval_status', User::APPROVAL_PENDING)->count(),
            'approved' => $this->baseQuery()->where('approval_status', User::APPROVAL_APPROVED)->count(),
            'rejected' => $this->baseQuery()->where('approval_status', User::APPROVAL_REJECTED)->count(),
        ];

        return view('superadmin.users', compact('users', 'counts', 'q', 'status'));
    }

    public function create(): View
    {
        return view('superadmin.user-create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')],
            'username' => ['required', 'string', 'min:4', 'max:60', 'alpha_dash', Rule::unique('users', 'username')],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', Rule::in([User::ROLE_USER, User::ROLE_ADMIN])],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'username' => Str::lower($data['username']),
            'phone' => $data['phone'] ?? null,
            'password' => $data['password'],
            'primary_role' => ucfirst($data['role']),
            'status' => User::STATUS_ACTIVE,
            'approval_status' => User::APPROVAL_APPROVED, // langsung aktif karena dibuat superadmin
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        AuditLogger::log($request->user()->id, 'superadmin.user_created', 'Membuat akun '.$data['role'].' baru: '.$user->email);

        return redirect()->route('superadmin.users.index')->with('status', 'Akun '.$data['role'].' berhasil dibuat dan langsung aktif.');
    }

    public function approve(Request $request, User $user): RedirectResponse
    {
        abort_if($user->isSuperAdmin(), 404);

        $user->update([
            'approval_status' => User::APPROVAL_APPROVED,
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'rejection_reason' => null,
            'status' => User::STATUS_ACTIVE,
        ]);

        AuditLogger::log($request->user()->id, 'superadmin.user_approved', 'Menyetujui akun: '.$user->email);

        return back()->with('status', 'Akun telah disetujui dan dapat login.');
    }

    public function reject(Request $request, User $user): RedirectResponse
    {
        abort_if($user->isSuperAdmin(), 404);

        $data = $request->validate(['reason' => ['nullable', 'string', 'max:255']]);

        $user->update([
            'approval_status' => User::APPROVAL_REJECTED,
            'rejection_reason' => $data['reason'] ?? null,
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        AuditLogger::log($request->user()->id, 'superadmin.user_rejected', 'Menolak akun: '.$user->email);

        return back()->with('status', 'Pendaftaran ditolak.');
    }

    public function toggleSuspend(Request $request, User $user): RedirectResponse
    {
        abort_if($user->isSuperAdmin(), 404);

        $new = $user->status === User::STATUS_ACTIVE ? User::STATUS_SUSPENDED : User::STATUS_ACTIVE;
        $user->update(['status' => $new]);

        AuditLogger::log($request->user()->id, 'superadmin.user_status', 'Ubah status '.$user->email.' → '.$new);

        return back()->with('status', 'Status akun diubah menjadi '.$new.'.');
    }

    public function changeRole(Request $request, User $user): RedirectResponse
    {
        abort_if($user->isSuperAdmin(), 404);

        $data = $request->validate(['role' => ['required', Rule::in([User::ROLE_USER, User::ROLE_ADMIN])]]);

        $user->update(['primary_role' => ucfirst($data['role'])]);

        AuditLogger::log($request->user()->id, 'superadmin.user_role', 'Ubah role '.$user->email.' → '.$data['role']);

        return back()->with('status', 'Role diperbarui.');
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        abort_if($user->isSuperAdmin(), 404);

        $data = $request->validate(['password' => ['required', 'string', 'min:8']]);

        $user->forceFill(['password' => Hash::make($data['password'])])->save();

        AuditLogger::log($request->user()->id, 'superadmin.user_reset_password', 'Reset password untuk '.$user->email);

        return back()->with('status', 'Password user berhasil direset.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_if($user->isSuperAdmin(), 404);

        $email = $user->email;
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
        }
        DB::table('audit_logs')->where('actor_user_id', $user->id)->update(['actor_user_id' => null]);
        $user->delete();
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');
        }

        AuditLogger::log($request->user()->id, 'superadmin.user_deleted', 'Menghapus akun: '.$email);

        return back()->with('status', 'Akun dihapus.');
    }
}
