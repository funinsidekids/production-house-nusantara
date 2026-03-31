@extends('layouts/contentNavbarLayout')

@section('title', 'User - Management')

@section('content')
<div class="row g-6">
    <div class="col-12">
        @if (session('success'))
            <div class="alert alert-success mb-0">{{ session('success') }}</div>
        @endif
        @if (session('generated_temp_password'))
            <div class="alert alert-warning mb-0">
                <strong>Temporary Password:</strong> {{ session('generated_temp_password') }} ({{ session('generated_temp_password_email') }})
            </div>
        @endif
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <h4 class="card-title text-primary mb-1">USER · Management</h4>
                    <p class="mb-0">Daftar pengguna dashboard untuk monitoring akun internal.</p>
                </div>
                <a href="{{ route('dashboard-user-create') }}" class="btn btn-primary">Create User</a>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <p class="mb-1">Total Users</p>
                <h4 class="mb-0">{{ number_format($totalUsers) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Multi Role</th>
                                <th>Department</th>
                                <th>Status</th>
                                <th>Registered At</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->primary_role ?: '-' }}</td>
                                    <td>
                                        @forelse ((array) ($user->role_slugs ?? []) as $roleSlug)
                                            <span class="badge bg-label-primary me-1">{{ $roleSlug }}</span>
                                        @empty
                                            <span class="text-muted">-</span>
                                        @endforelse
                                    </td>
                                    <td>{{ $user->department ?: '-' }}</td>
                                    <td>
                                        @if ($user->suspended_at)
                                            <span class="badge bg-danger">Suspended</span>
                                        @else
                                            <span class="badge bg-success">Active</span>
                                        @endif
                                    </td>
                                    <td>{{ optional($user->created_at)->toDateTimeString() }}</td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->id }}">Edit</button>
                                        <form method="POST" action="{{ route('dashboard-user-management.toggle-suspend', ['user' => $user->id]) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm {{ $user->suspended_at ? 'btn-outline-success' : 'btn-outline-danger' }}" type="submit">{{ $user->suspended_at ? 'Unsuspend' : 'Suspend' }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('dashboard-user-management.reset-password', ['user' => $user->id]) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-warning" type="submit">Reset Password</button>
                                        </form>
                                    </td>
                                </tr>
                                <div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('dashboard-user-management.update', ['user' => $user->id]) }}" enctype="multipart/form-data">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit User #{{ $user->id }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label">Full Name</label>
                                                            <input class="form-control" name="full_name" value="{{ $user->name }}" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Email</label>
                                                            <input class="form-control" type="email" name="email" value="{{ $user->email }}" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Phone</label>
                                                            <input class="form-control" name="phone" value="{{ $user->phone }}" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Profile Picture (Optional)</label>
                                                            <input class="form-control" type="file" name="profile_picture" accept=".jpg,.jpeg,.png,.webp">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label">Role</label>
                                                            <select class="form-select" name="primary_role" required>
                                                                @foreach ($roles as $role)
                                                                    <option value="{{ $role }}" @selected(($user->primary_role ?? '') === $role)>{{ $role }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-8">
                                                            <label class="form-label">Multi Role Assignment</label>
                                                            <select class="form-select" name="role_slugs[]" multiple size="4">
                                                                @foreach ($roleDefinitions as $roleDefinition)
                                                                    <option value="{{ $roleDefinition['slug'] }}" @selected(collect((array) ($user->role_slugs ?? []))->contains($roleDefinition['slug']))>{{ $roleDefinition['name'] }} ({{ $roleDefinition['slug'] }})</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label">Department</label>
                                                            <select class="form-select" name="department" required>
                                                                @foreach ($departments as $department)
                                                                    <option value="{{ $department }}" @selected(($user->department ?? '') === $department)>{{ $department }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label">Employment</label>
                                                            <select class="form-select" name="employment_status" required>
                                                                @foreach ($employmentStatuses as $status)
                                                                    <option value="{{ $status }}" @selected(($user->employment_status ?? '') === $status)>{{ Str::headline($status) }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label">Contract Start</label>
                                                            <input class="form-control" type="date" name="contract_start_at" value="{{ optional($user->contract_start_at)->toDateString() }}">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label">Contract End</label>
                                                            <input class="form-control" type="date" name="contract_end_at" value="{{ optional($user->contract_end_at)->toDateString() }}">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label">Auto Suspend</label>
                                                            <select class="form-select" name="auto_suspend_on_expiry">
                                                                <option value="1" @selected(($user->auto_suspend_on_expiry ?? true) === true)>ON</option>
                                                                <option value="0" @selected(($user->auto_suspend_on_expiry ?? true) === false)>OFF</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">Belum ada user.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
