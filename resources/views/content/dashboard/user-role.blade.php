@extends('layouts/contentNavbarLayout')

@section('title', 'User - Role')

@section('content')
<div class="row g-6">
    @php
        $selected = $selectedRole ?? null;
        $permissions = is_array($selected['permissions'] ?? null) ? $selected['permissions'] : [];
        $modules = ['dashboard', 'cms', 'calendar', 'finance', 'users'];
        $actions = ['create', 'read', 'update', 'delete', 'export', 'publish'];
        $resourceScope = old('resource_scope', $selected['resource_scope'] ?? 'own_project');
        $baseRoleSlug = old('base_role_slug', $selected['base_role_slug'] ?? '');
    @endphp
    <div class="col-12">
        <div class="card">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <h4 class="card-title text-primary mb-1">USER · Role</h4>
                    <p class="mb-0">Matriks role internal untuk kontrol akses dashboard.</p>
                </div>
                <a href="{{ route('dashboard-user-management') }}" class="btn btn-outline-secondary">Kembali ke Management</a>
            </div>
        </div>
    </div>
    <div class="col-12">
        @if (session('success'))
            <div class="alert alert-success mb-0">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger mb-0">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger mb-0">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('dashboard-user-role.store') }}" class="row g-3">
                    @csrf
                    <input type="hidden" name="original_slug" value="{{ old('original_slug', $selected['slug'] ?? '') }}">
                    <div class="col-12"><h6 class="mb-0">Role Basics</h6></div>
                    <div class="col-md-4">
                        <label class="form-label">Role Name</label>
                        <input class="form-control" name="role_name" value="{{ old('role_name', $selected['name'] ?? '') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Role Slug</label>
                        <input class="form-control" name="role_slug" value="{{ old('role_slug', $selected['slug'] ?? '') }}" required @if(($selected['slug'] ?? '') !== '') readonly @endif>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Color Label</label>
                        <input class="form-control" name="color_label" type="color" value="{{ old('color_label', $selected['color_label'] ?? '#6c757d') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" rows="2" name="description">{{ old('description', $selected['description'] ?? '') }}</textarea>
                    </div>

                    <div class="col-12"><h6 class="mb-0">Permission Matrix</h6></div>
                    <div class="col-12 table-responsive">
                        <table class="table table-sm table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>Module</th>
                                    @foreach ($actions as $action)
                                        <th class="text-center">{{ strtoupper($action) }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($modules as $module)
                                    <tr>
                                        <td>{{ strtoupper($module) }}</td>
                                        @foreach ($actions as $action)
                                            @php
                                                $checked = old("permissions.{$module}.{$action}", (string) (($permissions[$module][$action] ?? 0))) === '1';
                                            @endphp
                                            <td class="text-center">
                                                <input type="hidden" name="permissions[{{ $module }}][{{ $action }}]" value="0">
                                                <input type="checkbox" name="permissions[{{ $module }}][{{ $action }}]" value="1" @checked($checked)>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Resource Scope</label>
                        <select class="form-select" name="resource_scope">
                            <option value="own_project" @selected($resourceScope === 'own_project')>Own Project</option>
                            <option value="all_projects" @selected($resourceScope === 'all_projects')>All Projects</option>
                            <option value="specific_department" @selected($resourceScope === 'specific_department')>Specific Department</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Specific Department</label>
                        <select class="form-select" name="resource_department">
                            <option value="">-</option>
                            @foreach ($departmentOptions as $department)
                                <option value="{{ $department }}" @selected(old('resource_department', $selected['resource_department'] ?? '') === $department)>{{ $department }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Base Role (Inheritance)</label>
                        <select class="form-select" name="base_role_slug">
                            <option value="">None</option>
                            @foreach ($roles as $roleItem)
                                <option value="{{ $roleItem['slug'] }}" @selected($baseRoleSlug === $roleItem['slug'])>{{ $roleItem['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Override Add (comma)</label>
                        <input class="form-control" name="override_add" value="{{ old('override_add', implode(', ', (array) ($selected['override_add'] ?? []))) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Override Remove (comma)</label>
                        <input class="form-control" name="override_remove" value="{{ old('override_remove', implode(', ', (array) ($selected['override_remove'] ?? []))) }}">
                    </div>

                    <div class="col-12"><h6 class="mb-0">Dynamic Role Assignment</h6></div>
                    <div class="col-md-3">
                        <label class="form-label">Time Start</label>
                        <input class="form-control" type="date" name="time_based_start" value="{{ old('time_based_start', $selected['dynamic']['time_start'] ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Time End</label>
                        <input class="form-control" type="date" name="time_based_end" value="{{ old('time_based_end', $selected['dynamic']['time_end'] ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Project Scope</label>
                        <select class="form-select" name="project_based_role">
                            <option value="">None</option>
                            @foreach ($projectOptions as $projectId => $projectTitle)
                                <option value="{{ $projectId }}" @selected(old('project_based_role', $selected['dynamic']['project_scope'] ?? '') === $projectId)>{{ $projectTitle }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Office IP Only</label>
                        <input class="form-control" name="office_ip_only" value="{{ old('office_ip_only', $selected['dynamic']['office_ip_only'] ?? '') }}" placeholder="103.21.244.0/24">
                    </div>

                    <div class="col-12 d-flex gap-2">
                        <button class="btn btn-primary" type="submit">Simpan Role</button>
                        <a class="btn btn-outline-secondary" href="{{ route('dashboard-user-role') }}">Reset Form</a>
                    </div>
                </form>
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
                                <th>Role</th>
                                <th>Slug</th>
                                <th>Description</th>
                                <th>Scope</th>
                                <th>Color</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($roles as $role)
                                <tr>
                                    <td>{{ $role['name'] }}</td>
                                    <td><code>{{ $role['slug'] }}</code></td>
                                    <td>{{ $role['description'] }}</td>
                                    <td>{{ strtoupper($role['resource_scope'] ?? 'own_project') }}</td>
                                    <td><span class="badge bg-secondary">{{ $role['color_label'] ?? '#6c757d' }}</span></td>
                                    <td class="text-end">
                                        <a href="{{ route('dashboard-user-role', ['edit' => $role['slug']]) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-body d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Audit & Compliance</h6>
                <div class="d-flex gap-2">
                    <a href="{{ route('dashboard-user-role.export-csv') }}" class="btn btn-sm btn-outline-primary">Export CSV</a>
                    <a href="{{ route('dashboard-user-role.export-pdf') }}" class="btn btn-sm btn-primary">Export PDF</a>
                    <form method="POST" action="{{ route('dashboard-user-role.rollback') }}">
                        @csrf
                        <button class="btn btn-sm btn-outline-danger" type="submit">Rollback Last Change</button>
                    </form>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Role</th>
                            <th>Action</th>
                            <th>Changed At</th>
                            <th>Changed By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($history as $item)
                            <tr>
                                <td>{{ $item['role_slug'] ?? '-' }}</td>
                                <td>{{ strtoupper($item['action'] ?? '-') }}</td>
                                <td>{{ $item['changed_at'] ?? '-' }}</td>
                                <td>{{ $item['changed_by'] ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">Belum ada role history.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
