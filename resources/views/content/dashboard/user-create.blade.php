@extends('layouts/contentNavbarLayout')

@section('title', 'User - Create User')

@section('content')
<div class="row g-6">
    <div class="col-12">
        <div class="card">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <h4 class="card-title text-primary mb-1">USER · Create User</h4>
                    <p class="mb-0">User Onboarding dengan provisioning akses otomatis berdasarkan role & project.</p>
                </div>
                <a href="{{ route('dashboard-user-management') }}" class="btn btn-outline-secondary">Kembali ke Management</a>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('activation_link'))
                    <div class="alert alert-warning">
                        <div><strong>Activation Link:</strong> {{ session('activation_link') }}</div>
                    </div>
                @endif
                @if (session('two_fa_qr'))
                    <div class="alert alert-info">
                        <div class="mb-2">QR Code Google Authenticator:</div>
                        <img src="{{ session('two_fa_qr') }}" alt="2FA QR" style="max-height:220px;">
                    </div>
                @endif

                <form method="POST" action="{{ route('dashboard-user-create.store') }}" enctype="multipart/form-data" class="row g-4">
                    @csrf

                    <div class="col-12"><h6 class="mb-0">Identity Data</h6></div>
                    <div class="col-md-6">
                        <label class="form-label">Full Name</label>
                        <input class="form-control" name="full_name" value="{{ old('full_name') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email Corporate</label>
                        <input class="form-control" type="email" name="email" value="{{ old('email') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone/WhatsApp (+62...)</label>
                        <input class="form-control" name="phone" value="{{ old('phone', '+62') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Profile Picture (Max 2MB, Auto-crop 1:1)</label>
                        <input class="form-control" type="file" name="profile_picture" accept=".jpg,.jpeg,.png,.webp" required>
                    </div>

                    <div class="col-12"><h6 class="mb-0">Security Credentials</h6></div>
                    <div class="col-md-4">
                        <label class="form-label">Password</label>
                        <input class="form-control" type="password" name="password" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Konfirmasi Password</label>
                        <input class="form-control" type="password" name="password_confirmation" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">2FA Setup</label>
                        <select class="form-select" name="enable_2fa">
                            <option value="1" @selected(old('enable_2fa') === '1')>Enable</option>
                            <option value="0" @selected(old('enable_2fa', '0') === '0')>Disable</option>
                        </select>
                    </div>
                    @for ($i = 0; $i < 3; $i++)
                        <div class="col-md-6">
                            <label class="form-label">Security Question {{ $i + 1 }}</label>
                            <input class="form-control" list="securityQuestionOptions" name="security_questions[{{ $i }}][question]" value="{{ old("security_questions.{$i}.question") }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Security Answer {{ $i + 1 }}</label>
                            <input class="form-control" name="security_questions[{{ $i }}][answer]" value="{{ old("security_questions.{$i}.answer") }}">
                        </div>
                    @endfor
                    <datalist id="securityQuestionOptions">
                        @foreach ($securityQuestionOptions as $option)
                            <option value="{{ $option }}"></option>
                        @endforeach
                    </datalist>

                    <div class="col-12"><h6 class="mb-0">Assignment & Context</h6></div>
                    <div class="col-md-4">
                        <label class="form-label">Primary Role</label>
                        <select class="form-select" name="primary_role" required>
                            @foreach ($roles as $role)
                                <option value="{{ $role }}" @selected(old('primary_role') === $role)>{{ $role }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Multi Role Assignment</label>
                        <select class="form-select" name="role_slugs[]" multiple size="4">
                            @foreach ($roleDefinitions as $roleDefinition)
                                <option value="{{ $roleDefinition['slug'] }}" @selected(collect(old('role_slugs', []))->contains($roleDefinition['slug']))>{{ $roleDefinition['name'] }} ({{ $roleDefinition['slug'] }})</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Satu user bisa memiliki beberapa role.</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Department</label>
                        <select class="form-select" name="department" required>
                            @foreach ($departments as $department)
                                <option value="{{ $department }}" @selected(old('department') === $department)>{{ $department }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Employment Status</label>
                        <select class="form-select" name="employment_status" required>
                            @foreach ($employmentStatuses as $status)
                                <option value="{{ $status }}" @selected(old('employment_status') === $status)>{{ Str::headline($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Assigned Projects (active only)</label>
                        <select class="form-select" name="assigned_projects[]" multiple size="6">
                            @foreach ($projects as $project)
                                <option value="{{ $project['id'] }}" @selected(collect(old('assigned_projects', []))->contains($project['id']))>{{ $project['title'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Contract Start</label>
                        <input class="form-control" type="date" name="contract_start_at" value="{{ old('contract_start_at') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Contract End</label>
                        <input class="form-control" type="date" name="contract_end_at" value="{{ old('contract_end_at') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Auto-suspend when expired</label>
                        <select class="form-select" name="auto_suspend_on_expiry">
                            <option value="1" @selected(old('auto_suspend_on_expiry', '1') === '1')>ON</option>
                            <option value="0" @selected(old('auto_suspend_on_expiry') === '0')>OFF</option>
                        </select>
                    </div>
                    <div class="col-12"><h6 class="mb-0">Dynamic Role Assignment</h6></div>
                    <div class="col-md-3">
                        <label class="form-label">Role Context Slug</label>
                        <input class="form-control" name="role_assignment_contexts[0][role_slug]" value="{{ old('role_assignment_contexts.0.role_slug') }}" placeholder="editor">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Context Time Start</label>
                        <input class="form-control" type="date" name="role_assignment_contexts[0][time_start]" value="{{ old('role_assignment_contexts.0.time_start') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Context Time End</label>
                        <input class="form-control" type="date" name="role_assignment_contexts[0][time_end]" value="{{ old('role_assignment_contexts.0.time_end') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Office IP Only</label>
                        <input class="form-control" name="role_assignment_contexts[0][office_ip_only]" value="{{ old('role_assignment_contexts.0.office_ip_only') }}" placeholder="103.21.244.0/24">
                    </div>

                    <div class="col-12"><h6 class="mb-0">Notification & Welcome</h6></div>
                    <div class="col-md-3">
                        <label class="form-label">Send Welcome Email</label>
                        <select class="form-select" name="send_welcome_email">
                            <option value="1" @selected(old('send_welcome_email', '1') === '1')>Yes</option>
                            <option value="0" @selected(old('send_welcome_email') === '0')>No</option>
                        </select>
                    </div>
                    <div class="col-md-9">
                        <label class="form-label">CC Production Manager / HR</label>
                        <input class="form-control" name="welcome_cc" value="{{ old('welcome_cc') }}" placeholder="hr@domain.com, manager@domain.com">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Welcome Email Template</label>
                        <textarea class="form-control" rows="5" name="welcome_template">{{ old('welcome_template') }}</textarea>
                        <div class="form-text">
                            @verbatim
                                Placeholder yang tersedia: {{name}}, {{username}}, {{email}}, {{password}}, {{role}}, {{date}}, {{login_url}}, {{activation_link}}, {{website}}, {{support_email}}.
                            @endverbatim
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Slack/WhatsApp Notification</label>
                        <select class="form-select" name="notify_slack_whatsapp">
                            <option value="0" @selected(old('notify_slack_whatsapp', '0') === '0')>No</option>
                            <option value="1" @selected(old('notify_slack_whatsapp') === '1')>Yes</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <button class="btn btn-primary" type="submit">Create User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
