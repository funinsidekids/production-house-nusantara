<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\LandingSetting;
use App\Models\User;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserManagementController extends Controller
{
    private const ACTIVATION_HOURS = 24;

    public function index(): View
    {
        $this->autoSuspendExpiredUsers();
        $selectColumns = ['id', 'name', 'email', 'created_at'];
        foreach (['phone', 'primary_role', 'role_slugs', 'role_assignment_contexts', 'department', 'employment_status', 'contract_start_at', 'contract_end_at', 'auto_suspend_on_expiry', 'suspended_at'] as $optionalColumn) {
            if (Schema::hasColumn('users', $optionalColumn)) {
                $selectColumns[] = $optionalColumn;
            }
        }
        $users = User::query()
            ->latest('created_at')
            ->limit(300)
            ->get($selectColumns);
        $roleDefinitions = $this->roleDefinitions();

        return view('content.dashboard.user-management', [
            'users' => $users,
            'totalUsers' => User::query()->count(),
            'roles' => $this->roleOptions(),
            'departments' => $this->departmentOptions(),
            'employmentStatuses' => $this->employmentStatusOptions(),
            'roleDefinitions' => $roleDefinitions,
        ]);
    }

    public function create(): View|ViewFactory
    {
        $projects = $this->activeProjects();

        return view('content.dashboard.user-create', [
            'roles' => $this->roleOptions(),
            'roleDefinitions' => $this->roleDefinitions(),
            'departments' => $this->departmentOptions(),
            'employmentStatuses' => $this->employmentStatusOptions(),
            'projects' => $projects,
            'securityQuestionOptions' => [
                'Nama ibu kandung?',
                'Nama kota lahir?',
                'Nama sekolah pertama?',
            ],
        ]);
    }

    public function role(Request $request): View
    {
        $roleDefinitions = $this->roleDefinitions();
        $editSlug = Str::lower(trim((string) $request->query('edit', '')));
        $selectedRole = collect($roleDefinitions)
            ->first(fn (array $role): bool => (string) ($role['slug'] ?? '') === $editSlug);
        $history = $this->roleHistoryPayload();

        return view('content.dashboard.user-role', [
            'roles' => $roleDefinitions,
            'selectedRole' => is_array($selectedRole) ? $selectedRole : null,
            'history' => $history,
            'departmentOptions' => $this->departmentOptions(),
            'projectOptions' => collect($this->activeProjects())->pluck('title', 'id')->all(),
        ]);
    }

    public function roleStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'original_slug' => ['nullable', 'string', 'max:120'],
            'role_name' => ['required', 'string', 'max:120'],
            'role_slug' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9_]+$/'],
            'description' => ['nullable', 'string', 'max:500'],
            'color_label' => ['nullable', 'string', 'max:20'],
            'base_role_slug' => ['nullable', 'string', 'max:120'],
            'override_add' => ['nullable', 'string', 'max:1000'],
            'override_remove' => ['nullable', 'string', 'max:1000'],
            'resource_scope' => ['required', Rule::in(['own_project', 'all_projects', 'specific_department'])],
            'resource_department' => ['nullable', 'string', 'max:120'],
            'time_based_start' => ['nullable', 'date'],
            'time_based_end' => ['nullable', 'date', 'after_or_equal:time_based_start'],
            'project_based_role' => ['nullable', 'string', 'max:120'],
            'office_ip_only' => ['nullable', 'string', 'max:120'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['array'],
            'permissions.*.*' => ['nullable', 'in:0,1'],
        ]);

        $definitions = $this->roleDefinitions();
        $originalSlug = Str::lower(trim((string) ($data['original_slug'] ?? '')));
        $roleSlug = Str::lower(trim((string) $data['role_slug']));
        if ($originalSlug !== '' && $originalSlug !== $roleSlug) {
            return redirect()->route('dashboard-user-role', ['edit' => $originalSlug])->with('error', 'Role Slug bersifat immutable.');
        }
        $index = collect($definitions)->search(fn (array $item): bool => (string) ($item['slug'] ?? '') === $roleSlug);
        $permissionMatrix = $this->normalizePermissionMatrix($data['permissions'] ?? []);
        $newRole = [
            'name' => trim((string) $data['role_name']),
            'slug' => $roleSlug,
            'description' => trim((string) ($data['description'] ?? '')),
            'color_label' => trim((string) ($data['color_label'] ?? '#6c757d')),
            'permissions' => $permissionMatrix,
            'resource_scope' => (string) $data['resource_scope'],
            'resource_department' => trim((string) ($data['resource_department'] ?? '')),
            'base_role_slug' => Str::lower(trim((string) ($data['base_role_slug'] ?? ''))),
            'override_add' => $this->csvToList((string) ($data['override_add'] ?? '')),
            'override_remove' => $this->csvToList((string) ($data['override_remove'] ?? '')),
            'dynamic' => [
                'time_start' => (string) ($data['time_based_start'] ?? ''),
                'time_end' => (string) ($data['time_based_end'] ?? ''),
                'project_scope' => trim((string) ($data['project_based_role'] ?? '')),
                'office_ip_only' => trim((string) ($data['office_ip_only'] ?? '')),
            ],
            'updated_at' => now()->toDateTimeString(),
            'updated_by' => (int) (Auth::id() ?? 0),
        ];
        if ($index === false) {
            $definitions[] = $newRole;
            $action = 'created';
        } else {
            $definitions[$index] = $newRole;
            $action = 'updated';
        }
        $this->saveRoleDefinitions($definitions);
        $this->appendRoleHistory($roleSlug, $action, $newRole);

        return redirect()->route('dashboard-user-role', ['edit' => $roleSlug])->with('success', 'Role berhasil disimpan.');
    }

    public function roleRollback(): RedirectResponse
    {
        $history = $this->roleHistoryPayload();
        $last = $history[0] ?? null;
        if (! is_array($last) || ! isset($last['snapshot']) || ! is_array($last['snapshot'])) {
            return redirect()->route('dashboard-user-role')->with('error', 'Tidak ada history role untuk rollback.');
        }
        $snapshot = $last['snapshot'];
        $slug = (string) ($last['role_slug'] ?? '');
        $definitions = $this->roleDefinitions();
        $index = collect($definitions)->search(fn (array $item): bool => (string) ($item['slug'] ?? '') === $slug);
        if ($index === false) {
            $definitions[] = $snapshot;
        } else {
            $definitions[$index] = $snapshot;
        }
        $this->saveRoleDefinitions($definitions);
        $this->appendRoleHistory($slug, 'rollback', $snapshot);

        return redirect()->route('dashboard-user-role', ['edit' => $slug])->with('success', 'Rollback role berhasil.');
    }

    public function roleExportCsv(): StreamedResponse
    {
        $rows = $this->roleDefinitions();
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="role-matrix.csv"',
        ];

        return response()->stream(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            if (! is_resource($handle)) {
                return;
            }
            fputcsv($handle, ['Role Name', 'Slug', 'Description', 'Color', 'Resource Scope', 'Department', 'Base Role', 'Permissions JSON']);
            foreach ($rows as $role) {
                fputcsv($handle, [
                    (string) ($role['name'] ?? ''),
                    (string) ($role['slug'] ?? ''),
                    (string) ($role['description'] ?? ''),
                    (string) ($role['color_label'] ?? ''),
                    (string) ($role['resource_scope'] ?? ''),
                    (string) ($role['resource_department'] ?? ''),
                    (string) ($role['base_role_slug'] ?? ''),
                    json_encode($role['permissions'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }

    public function roleExportPdf(): Response
    {
        $rows = $this->roleDefinitions();
        $documentNumber = 'PHN-RM-'.now()->format('Ymd-His');
        $generatedBy = 'System';
        if (Auth::user() instanceof User) {
            $generatedBy = trim((string) (Auth::user()->name ?? ''));
            if ($generatedBy === '') {
                $generatedBy = trim((string) (Auth::user()->email ?? 'System'));
            }
        }
        $html = view('content.dashboard.user-role-pdf', [
            'roles' => $rows,
            'generatedAt' => now()->toDateTimeString(),
            'generatedBy' => $generatedBy,
            'documentNumber' => $documentNumber,
            'companyName' => 'PRODUCTION HOUSE NUSANTARA',
            'tagline' => 'Film • Commercial • Documentary • Creative Production',
            'logoDataUri' => $this->pdfLogoDataUri(),
        ])->render();

        $options = new Options;
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $canvas = $dompdf->getCanvas();
        $font = $dompdf->getFontMetrics()->getFont('DejaVu Sans', 'normal');
        $canvas->page_text(
            20,
            $canvas->get_height() - 20,
            "Doc {$documentNumber} • Page {PAGE_NUM} of {PAGE_COUNT} • Generated by {$generatedBy}",
            $font,
            9,
            [0.25, 0.25, 0.25]
        );

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="role-matrix-phn.pdf"',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'min:3', 'max:180', 'regex:/^[A-Za-z\s]+$/'],
            'email' => ['required', 'email', 'max:180', Rule::unique('users', 'email')],
            'phone' => ['required', 'regex:/^\+62[0-9]{8,13}$/'],
            'password' => ['required', 'string', 'min:8', 'max:120', 'confirmed'],
            'profile_picture' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'enable_2fa' => ['nullable', 'in:0,1'],
            'security_questions' => ['nullable', 'array'],
            'security_questions.*.question' => ['nullable', 'string', 'max:200'],
            'security_questions.*.answer' => ['nullable', 'string', 'max:200'],
            'primary_role' => ['required', 'string', 'max:80'],
            'role_slugs' => ['nullable', 'array'],
            'role_slugs.*' => ['string', 'max:120'],
            'role_assignment_contexts' => ['nullable', 'array'],
            'role_assignment_contexts.*.role_slug' => ['nullable', 'string', 'max:120'],
            'role_assignment_contexts.*.time_start' => ['nullable', 'date'],
            'role_assignment_contexts.*.time_end' => ['nullable', 'date'],
            'role_assignment_contexts.*.project_scope' => ['nullable', 'string', 'max:150'],
            'role_assignment_contexts.*.office_ip_only' => ['nullable', 'string', 'max:120'],
            'department' => ['required', 'string', 'max:80'],
            'assigned_projects' => ['nullable', 'array'],
            'assigned_projects.*' => ['string', 'max:150'],
            'employment_status' => ['required', Rule::in(['full-time', 'freelance', 'intern'])],
            'contract_start_at' => ['nullable', 'date'],
            'contract_end_at' => ['nullable', 'date', 'after_or_equal:contract_start_at'],
            'auto_suspend_on_expiry' => ['nullable', 'in:0,1'],
            'send_welcome_email' => ['nullable', 'in:0,1'],
            'welcome_template' => ['nullable', 'string', 'max:5000'],
            'welcome_cc' => ['nullable', 'string', 'max:400'],
            'notify_slack_whatsapp' => ['nullable', 'in:0,1'],
        ]);

        $sendWelcome = (string) ($data['send_welcome_email'] ?? '1') === '1';
        $manualPassword = (string) $data['password'];
        $enable2fa = (string) ($data['enable_2fa'] ?? '0') === '1';
        $autoSuspendOnExpiry = (string) ($data['auto_suspend_on_expiry'] ?? '1') === '1';
        $notifySlackWhatsapp = (string) ($data['notify_slack_whatsapp'] ?? '0') === '1';

        $profilePicturePath = $this->storeCroppedProfilePicture($request->file('profile_picture'));
        $activationTokenPlain = Str::lower(Str::random(64));
        $activationTokenHash = hash('sha256', $activationTokenPlain);
        $securityQuestions = $this->normalizedSecurityQuestions($data['security_questions'] ?? []);
        $twoFactorSecret = $enable2fa ? $this->generateTwoFactorSecret() : null;
        $roleSlugs = $this->sanitizeRoleSlugs($data['role_slugs'] ?? []);
        if (count($roleSlugs) === 0) {
            $roleSlugs = [Str::lower(trim((string) $data['primary_role']))];
        }
        $roleAssignmentContexts = $this->sanitizeRoleAssignmentContexts($data['role_assignment_contexts'] ?? [], $roleSlugs);
        $permissionsPayload = $this->permissionsPayloadForRoles($roleSlugs);

        $user = DB::transaction(function () use (
            $data,
            $manualPassword,
            $enable2fa,
            $twoFactorSecret,
            $securityQuestions,
            $roleSlugs,
            $roleAssignmentContexts,
            $profilePicturePath,
            $activationTokenHash,
            $autoSuspendOnExpiry,
            $permissionsPayload,
            $sendWelcome
        ): User {
            $payload = [
                'name' => trim((string) $data['full_name']),
                'email' => trim((string) $data['email']),
                'phone' => trim((string) $data['phone']),
                'profile_picture_path' => $profilePicturePath,
                'password' => Hash::make($manualPassword),
                'force_password_change' => false,
                'two_factor_secret' => $enable2fa ? $twoFactorSecret : null,
                'security_questions' => $securityQuestions,
                'primary_role' => (string) $data['primary_role'],
                'role_slugs' => $roleSlugs,
                'role_assignment_contexts' => $roleAssignmentContexts,
                'department' => (string) $data['department'],
                'assigned_projects' => array_values($data['assigned_projects'] ?? []),
                'employment_status' => (string) $data['employment_status'],
                'contract_start_at' => $data['contract_start_at'] ?? null,
                'contract_end_at' => $data['contract_end_at'] ?? null,
                'auto_suspend_on_expiry' => $autoSuspendOnExpiry,
                'permissions_payload' => $permissionsPayload,
                'onboarded_by' => Auth::id(),
                'activation_token_hash' => $activationTokenHash,
                'activation_token_expires_at' => now()->addHours(self::ACTIVATION_HOURS),
                'welcome_email_sent_at' => $sendWelcome ? now() : null,
            ];
            $created = User::query()->create($this->filterExistingUserColumns($payload));

            $this->insertAuditLog('user.onboarding.created', 'user', $created->id, [
                'created_email' => $created->email,
                'role' => $created->primary_role,
                'role_slugs' => $created->role_slugs,
                'department' => $created->department,
                'projects' => $created->assigned_projects,
                'employment_status' => $created->employment_status,
            ]);

            return $created;
        });

        $activationLink = route('dashboard-user-activate', [
            'userId' => $user->id,
            'token' => $activationTokenPlain,
        ]);
        $loginUrl = url('/login');
        $welcomeTemplate = trim((string) ($data['welcome_template'] ?? ''));
        if ($welcomeTemplate === '') {
            $welcomeTemplate = "Halo {{name}},\n\nSelamat datang di PRODUCTION HOUSE NUSANTARA.\n\nAkun Anda telah berhasil dibuat di sistem Production House Nusantara.\nSilakan gunakan informasi berikut untuk login ke dashboard.\n\n================================\n\nINFORMASI AKUN ANDA\n\nNama        : {{name}}\nUsername    : {{username}}\nEmail       : {{email}}\nPassword    : {{password}}\nRole        : {{role}}\nTanggal     : {{date}}\n\n================================\n\nLOGIN SISTEM\n\nSilakan login melalui link berikut:\n\n{{login_url}}\n\nSetelah login, Anda dapat mengakses:\n\n* Dashboard Production\n* Project Management\n* Script & Storyboard\n* Timeline & Schedule\n* Media & Editing Pipeline\n* Client & Order System\n* Talent & Crew Management\n* Finance (jika diizinkan)\n\nDemi keamanan, kami menyarankan Anda segera mengganti password setelah login pertama.\n\n================================\n\nTerima kasih telah bergabung dengan PRODUCTION HOUSE NUSANTARA.\n\nKami percaya bahwa karya besar lahir dari tim yang solid.\n\nSalam,\n\nPRODUCTION HOUSE NUSANTARA\nFilm • Commercial • Documentary • Creative Production\n\nWebsite : {{website}}\nSupport : {{support_email}}\n\n================================\n\nEmail ini dibuat otomatis oleh sistem.";
        }
        if ($sendWelcome) {
            $this->sendWelcomeEmail(
                $user,
                $manualPassword,
                $activationLink,
                $loginUrl,
                $welcomeTemplate,
                trim((string) ($data['welcome_cc'] ?? '')),
                $enable2fa ? $this->qrCodeUrl($user->email, (string) $twoFactorSecret) : null
            );
        }
        if ($notifySlackWhatsapp) {
            $this->dispatchOptionalWebhookNotifications($user, $activationLink);
        }

        return redirect()
            ->route('dashboard-user-create')
            ->with('success', 'User berhasil dibuat.')
            ->with('activation_link', $activationLink)
            ->with('two_fa_qr', $enable2fa ? $this->qrCodeUrl($user->email, (string) $twoFactorSecret) : null);
    }

    public function activate(int $userId, string $token): RedirectResponse
    {
        $user = User::query()->find($userId);
        if (! $user instanceof User) {
            return redirect('/')->with('error', 'Link aktivasi tidak valid.');
        }
        $expiresAt = optional($user->activation_token_expires_at)?->timestamp;
        if ($expiresAt === null || $expiresAt < time()) {
            return redirect('/')->with('error', 'Link aktivasi sudah kedaluwarsa.');
        }
        $incomingHash = hash('sha256', $token);
        if (! hash_equals((string) ($user->activation_token_hash ?? ''), $incomingHash)) {
            return redirect('/')->with('error', 'Link aktivasi tidak valid.');
        }

        $user->forceFill([
            'email_verified_at' => now(),
            'activation_token_hash' => null,
            'activation_token_expires_at' => null,
        ])->save();

        $this->insertAuditLog('user.onboarding.activated', 'user', $user->id, [
            'email' => $user->email,
        ]);

        return redirect('/login')->with('success', 'Akun berhasil diaktivasi. Silakan login.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'min:3', 'max:180', 'regex:/^[A-Za-z\s]+$/'],
            'email' => ['required', 'email', 'max:180', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['required', 'regex:/^\+62[0-9]{8,13}$/'],
            'primary_role' => ['required', 'string', 'max:80'],
            'role_slugs' => ['nullable', 'array'],
            'role_slugs.*' => ['string', 'max:120'],
            'role_assignment_contexts' => ['nullable', 'array'],
            'role_assignment_contexts.*.role_slug' => ['nullable', 'string', 'max:120'],
            'role_assignment_contexts.*.time_start' => ['nullable', 'date'],
            'role_assignment_contexts.*.time_end' => ['nullable', 'date'],
            'role_assignment_contexts.*.project_scope' => ['nullable', 'string', 'max:150'],
            'role_assignment_contexts.*.office_ip_only' => ['nullable', 'string', 'max:120'],
            'department' => ['required', 'string', 'max:80'],
            'employment_status' => ['required', Rule::in($this->employmentStatusOptions())],
            'contract_start_at' => ['nullable', 'date'],
            'contract_end_at' => ['nullable', 'date', 'after_or_equal:contract_start_at'],
            'auto_suspend_on_expiry' => ['nullable', 'in:0,1'],
            'assigned_projects' => ['nullable', 'array'],
            'assigned_projects.*' => ['string', 'max:150'],
            'profile_picture' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $roleSlugs = $this->sanitizeRoleSlugs($data['role_slugs'] ?? []);
        if (count($roleSlugs) === 0) {
            $roleSlugs = [Str::lower(trim((string) $data['primary_role']))];
        }
        $roleAssignmentContexts = $this->sanitizeRoleAssignmentContexts($data['role_assignment_contexts'] ?? [], $roleSlugs);
        $profilePicturePath = (string) ($user->profile_picture_path ?? '');
        $newProfile = $request->file('profile_picture');
        if ($newProfile instanceof UploadedFile) {
            $profilePicturePath = $this->storeCroppedProfilePicture($newProfile);
        }

        $user->forceFill($this->filterExistingUserColumns([
            'name' => trim((string) $data['full_name']),
            'email' => trim((string) $data['email']),
            'phone' => trim((string) $data['phone']),
            'profile_picture_path' => $profilePicturePath,
            'primary_role' => (string) $data['primary_role'],
            'role_slugs' => $roleSlugs,
            'role_assignment_contexts' => $roleAssignmentContexts,
            'department' => (string) $data['department'],
            'employment_status' => (string) $data['employment_status'],
            'contract_start_at' => $data['contract_start_at'] ?? null,
            'contract_end_at' => $data['contract_end_at'] ?? null,
            'auto_suspend_on_expiry' => (string) ($data['auto_suspend_on_expiry'] ?? '1') === '1',
            'assigned_projects' => array_values($data['assigned_projects'] ?? []),
            'permissions_payload' => $this->permissionsPayloadForRoles($roleSlugs),
        ]))->save();

        $this->insertAuditLog('user.management.updated', 'user', $user->id, [
            'email' => $user->email,
            'role' => $user->primary_role,
            'department' => $user->department,
        ]);

        return redirect()->route('dashboard-user-management')->with('success', 'User berhasil diupdate.');
    }

    public function toggleSuspend(User $user): RedirectResponse
    {
        $isSuspended = $user->suspended_at !== null;
        $user->forceFill($this->filterExistingUserColumns([
            'suspended_at' => $isSuspended ? null : now(),
        ]))->save();

        $this->insertAuditLog('user.management.suspend_toggle', 'user', $user->id, [
            'email' => $user->email,
            'is_suspended' => ! $isSuspended,
        ]);

        return redirect()->route('dashboard-user-management')
            ->with('success', $isSuspended ? 'User berhasil diaktifkan kembali.' : 'User berhasil disuspend.');
    }

    public function resetPassword(User $user): RedirectResponse
    {
        $temporaryPassword = Str::password(12, true, true, true, false);
        $user->forceFill($this->filterExistingUserColumns([
            'password' => Hash::make($temporaryPassword),
            'force_password_change' => true,
        ]))->save();

        $this->insertAuditLog('user.management.password_reset', 'user', $user->id, [
            'email' => $user->email,
        ]);

        Mail::raw(
            "Password akun kamu telah direset oleh admin.\n\nLogin URL: ".url('/login')."\nEmail: {$user->email}\nTemporary Password: {$temporaryPassword}\nSilakan ganti password saat login pertama.",
            function ($message) use ($user): void {
                $message->to($user->email)->subject('Reset Password PHN Dashboard');
            }
        );

        return redirect()->route('dashboard-user-management')
            ->with('success', 'Password user berhasil direset.')
            ->with('generated_temp_password', $temporaryPassword)
            ->with('generated_temp_password_email', $user->email);
    }

    private function normalizedSecurityQuestions(array $questions): array
    {
        $result = [];
        foreach ($questions as $item) {
            if (! is_array($item)) {
                continue;
            }
            $question = trim((string) ($item['question'] ?? ''));
            $answer = trim((string) ($item['answer'] ?? ''));
            if ($question === '' || $answer === '') {
                continue;
            }
            $result[] = [
                'question' => $question,
                'answer_hash' => Hash::make($answer),
            ];
        }

        return $result;
    }

    private function generateTwoFactorSecret(): string
    {
        return strtoupper(Str::random(32));
    }

    private function qrCodeUrl(string $email, string $secret): string
    {
        $label = rawurlencode('PHN:'.$email);
        $issuer = rawurlencode('PHN');
        $otpAuthUrl = "otpauth://totp/{$label}?secret={$secret}&issuer={$issuer}";

        return 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data='.rawurlencode($otpAuthUrl);
    }

    private function permissionsPayloadForRoles(array $roleSlugs): array
    {
        $definitions = $this->roleDefinitions();
        $indexed = collect($definitions)
            ->keyBy(fn (array $role): string => (string) ($role['slug'] ?? ''))
            ->all();
        $permissions = [];
        foreach ($roleSlugs as $slug) {
            $normalized = Str::lower(trim((string) $slug));
            if ($normalized === '') {
                continue;
            }
            $role = $indexed[$normalized] ?? null;
            if (! is_array($role)) {
                continue;
            }
            foreach ($this->flattenPermissionMatrix($this->resolveRolePermissionMatrix($role, $indexed)) as $permission) {
                $permissions[$permission] = true;
            }
            foreach (($role['override_add'] ?? []) as $permission) {
                $permissions[(string) $permission] = true;
            }
            foreach (($role['override_remove'] ?? []) as $permission) {
                unset($permissions[(string) $permission]);
            }
        }

        return array_values(array_keys($permissions));
    }

    private function resolveRolePermissionMatrix(array $role, array $indexedRoles, array $visited = []): array
    {
        $slug = Str::lower(trim((string) ($role['slug'] ?? '')));
        if ($slug !== '' && in_array($slug, $visited, true)) {
            return is_array($role['permissions'] ?? null) ? $role['permissions'] : [];
        }
        $visited[] = $slug;
        $matrix = is_array($role['permissions'] ?? null) ? $role['permissions'] : [];
        $baseSlug = Str::lower(trim((string) ($role['base_role_slug'] ?? '')));
        if ($baseSlug !== '' && isset($indexedRoles[$baseSlug]) && is_array($indexedRoles[$baseSlug])) {
            $baseMatrix = $this->resolveRolePermissionMatrix($indexedRoles[$baseSlug], $indexedRoles, $visited);
            foreach ($baseMatrix as $module => $actions) {
                if (! is_array($actions)) {
                    continue;
                }
                if (! isset($matrix[$module]) || ! is_array($matrix[$module])) {
                    $matrix[$module] = [];
                }
                foreach ($actions as $action => $enabled) {
                    if (! array_key_exists($action, $matrix[$module])) {
                        $matrix[$module][$action] = (int) $enabled;
                    }
                }
            }
        }

        return $matrix;
    }

    private function roleDefinitions(): array
    {
        $raw = (string) (LandingSetting::query()->where('key', 'cms_user_roles_payload')->value('value') ?? '');
        $decoded = json_decode($raw, true);
        if (! is_array($decoded) || count($decoded) === 0) {
            return $this->defaultRoleDefinitions();
        }

        return collect($decoded)
            ->filter(fn ($item): bool => is_array($item))
            ->map(function (array $item): array {
                return [
                    'name' => trim((string) ($item['name'] ?? '')),
                    'slug' => Str::lower(trim((string) ($item['slug'] ?? ''))),
                    'description' => trim((string) ($item['description'] ?? '')),
                    'color_label' => trim((string) ($item['color_label'] ?? '#6c757d')),
                    'permissions' => is_array($item['permissions'] ?? null) ? $item['permissions'] : [],
                    'resource_scope' => (string) ($item['resource_scope'] ?? 'own_project'),
                    'resource_department' => trim((string) ($item['resource_department'] ?? '')),
                    'base_role_slug' => Str::lower(trim((string) ($item['base_role_slug'] ?? ''))),
                    'override_add' => array_values(array_filter((array) ($item['override_add'] ?? []), fn ($value): bool => trim((string) $value) !== '')),
                    'override_remove' => array_values(array_filter((array) ($item['override_remove'] ?? []), fn ($value): bool => trim((string) $value) !== '')),
                    'dynamic' => is_array($item['dynamic'] ?? null) ? $item['dynamic'] : [],
                    'updated_at' => (string) ($item['updated_at'] ?? ''),
                    'updated_by' => (int) ($item['updated_by'] ?? 0),
                ];
            })
            ->filter(fn (array $item): bool => $item['name'] !== '' && $item['slug'] !== '')
            ->values()
            ->all();
    }

    private function saveRoleDefinitions(array $roles): void
    {
        LandingSetting::query()->updateOrCreate(
            ['key' => 'cms_user_roles_payload'],
            ['value' => json_encode(array_values($roles), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
        );
    }

    private function roleHistoryPayload(): array
    {
        $raw = (string) (LandingSetting::query()->where('key', 'cms_user_roles_history_payload')->value('value') ?? '');
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function appendRoleHistory(string $roleSlug, string $action, array $snapshot): void
    {
        $history = $this->roleHistoryPayload();
        array_unshift($history, [
            'role_slug' => $roleSlug,
            'action' => $action,
            'snapshot' => $snapshot,
            'changed_at' => now()->toDateTimeString(),
            'changed_by' => (int) (Auth::id() ?? 0),
        ]);
        LandingSetting::query()->updateOrCreate(
            ['key' => 'cms_user_roles_history_payload'],
            ['value' => json_encode(array_values(array_slice($history, 0, 300)), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
        );
    }

    private function defaultRoleDefinitions(): array
    {
        return [
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Super user',
                'color_label' => '#dc3545',
                'permissions' => [
                    'dashboard' => ['create' => 1, 'read' => 1, 'update' => 1, 'delete' => 1, 'export' => 1, 'publish' => 1],
                    'cms' => ['create' => 1, 'read' => 1, 'update' => 1, 'delete' => 1, 'export' => 1, 'publish' => 1],
                    'calendar' => ['create' => 1, 'read' => 1, 'update' => 1, 'delete' => 1, 'export' => 1, 'publish' => 1],
                    'finance' => ['create' => 1, 'read' => 1, 'update' => 1, 'delete' => 1, 'export' => 1, 'publish' => 1],
                    'users' => ['create' => 1, 'read' => 1, 'update' => 1, 'delete' => 1, 'export' => 1, 'publish' => 1],
                ],
                'resource_scope' => 'all_projects',
                'resource_department' => '',
                'base_role_slug' => '',
                'override_add' => ['*'],
                'override_remove' => [],
                'dynamic' => [],
                'updated_at' => now()->toDateTimeString(),
                'updated_by' => (int) (Auth::id() ?? 0),
            ],
            [
                'name' => 'Editor',
                'slug' => 'editor',
                'description' => 'Akses editing konten',
                'color_label' => '#198754',
                'permissions' => [
                    'dashboard' => ['create' => 0, 'read' => 1, 'update' => 0, 'delete' => 0, 'export' => 0, 'publish' => 0],
                    'cms' => ['create' => 1, 'read' => 1, 'update' => 1, 'delete' => 0, 'export' => 1, 'publish' => 1],
                    'calendar' => ['create' => 1, 'read' => 1, 'update' => 1, 'delete' => 0, 'export' => 0, 'publish' => 0],
                    'finance' => ['create' => 0, 'read' => 0, 'update' => 0, 'delete' => 0, 'export' => 0, 'publish' => 0],
                    'users' => ['create' => 0, 'read' => 1, 'update' => 0, 'delete' => 0, 'export' => 0, 'publish' => 0],
                ],
                'resource_scope' => 'own_project',
                'resource_department' => '',
                'base_role_slug' => '',
                'override_add' => [],
                'override_remove' => [],
                'dynamic' => [],
                'updated_at' => now()->toDateTimeString(),
                'updated_by' => (int) (Auth::id() ?? 0),
            ],
            [
                'name' => 'Director',
                'slug' => 'director',
                'description' => 'Approval project',
                'color_label' => '#0d6efd',
                'permissions' => [
                    'dashboard' => ['create' => 0, 'read' => 1, 'update' => 1, 'delete' => 0, 'export' => 1, 'publish' => 1],
                    'cms' => ['create' => 0, 'read' => 1, 'update' => 1, 'delete' => 0, 'export' => 1, 'publish' => 1],
                    'calendar' => ['create' => 0, 'read' => 1, 'update' => 1, 'delete' => 0, 'export' => 0, 'publish' => 0],
                    'finance' => ['create' => 0, 'read' => 1, 'update' => 0, 'delete' => 0, 'export' => 1, 'publish' => 0],
                    'users' => ['create' => 0, 'read' => 1, 'update' => 0, 'delete' => 0, 'export' => 0, 'publish' => 0],
                ],
                'resource_scope' => 'specific_department',
                'resource_department' => 'Production',
                'base_role_slug' => '',
                'override_add' => [],
                'override_remove' => [],
                'dynamic' => [],
                'updated_at' => now()->toDateTimeString(),
                'updated_by' => (int) (Auth::id() ?? 0),
            ],
            [
                'name' => 'Writer',
                'slug' => 'writer',
                'description' => 'Script & storyboard',
                'color_label' => '#6f42c1',
                'permissions' => [
                    'dashboard' => ['create' => 0, 'read' => 1, 'update' => 0, 'delete' => 0, 'export' => 0, 'publish' => 0],
                    'cms' => ['create' => 1, 'read' => 1, 'update' => 1, 'delete' => 0, 'export' => 0, 'publish' => 0],
                    'calendar' => ['create' => 1, 'read' => 1, 'update' => 1, 'delete' => 0, 'export' => 0, 'publish' => 0],
                    'finance' => ['create' => 0, 'read' => 0, 'update' => 0, 'delete' => 0, 'export' => 0, 'publish' => 0],
                    'users' => ['create' => 0, 'read' => 0, 'update' => 0, 'delete' => 0, 'export' => 0, 'publish' => 0],
                ],
                'resource_scope' => 'own_project',
                'resource_department' => '',
                'base_role_slug' => '',
                'override_add' => [],
                'override_remove' => [],
                'dynamic' => [],
                'updated_at' => now()->toDateTimeString(),
                'updated_by' => (int) (Auth::id() ?? 0),
            ],
            [
                'name' => 'Crew',
                'slug' => 'crew',
                'description' => 'Operational crew',
                'color_label' => '#fd7e14',
                'permissions' => [
                    'dashboard' => ['create' => 0, 'read' => 1, 'update' => 0, 'delete' => 0, 'export' => 0, 'publish' => 0],
                    'cms' => ['create' => 0, 'read' => 1, 'update' => 0, 'delete' => 0, 'export' => 0, 'publish' => 0],
                    'calendar' => ['create' => 1, 'read' => 1, 'update' => 1, 'delete' => 0, 'export' => 0, 'publish' => 0],
                    'finance' => ['create' => 0, 'read' => 0, 'update' => 0, 'delete' => 0, 'export' => 0, 'publish' => 0],
                    'users' => ['create' => 0, 'read' => 0, 'update' => 0, 'delete' => 0, 'export' => 0, 'publish' => 0],
                ],
                'resource_scope' => 'own_project',
                'resource_department' => '',
                'base_role_slug' => '',
                'override_add' => [],
                'override_remove' => [],
                'dynamic' => [],
                'updated_at' => now()->toDateTimeString(),
                'updated_by' => (int) (Auth::id() ?? 0),
            ],
        ];
    }

    private function roleOptions(): array
    {
        return collect($this->roleDefinitions())
            ->map(fn (array $role): string => (string) ($role['name'] ?? ''))
            ->filter(fn (string $name): bool => $name !== '')
            ->values()
            ->all();
    }

    private function normalizePermissionMatrix(array $permissions): array
    {
        $modules = ['dashboard', 'cms', 'calendar', 'finance', 'users'];
        $actions = ['create', 'read', 'update', 'delete', 'export', 'publish'];
        $normalized = [];
        foreach ($modules as $module) {
            $normalized[$module] = [];
            foreach ($actions as $action) {
                $normalized[$module][$action] = (int) ((string) ($permissions[$module][$action] ?? '0') === '1');
            }
        }

        return $normalized;
    }

    private function flattenPermissionMatrix(array $matrix): array
    {
        $permissions = [];
        foreach ($matrix as $module => $actions) {
            if (! is_array($actions)) {
                continue;
            }
            foreach ($actions as $action => $enabled) {
                if ((int) $enabled !== 1) {
                    continue;
                }
                $permissions[] = trim((string) $module).'.'.trim((string) $action);
            }
        }

        return array_values(array_unique(array_filter($permissions, fn ($permission): bool => $permission !== '.')));
    }

    private function csvToList(string $csv): array
    {
        return collect(explode(',', $csv))
            ->map(fn ($item): string => trim((string) $item))
            ->filter(fn ($item): bool => $item !== '')
            ->values()
            ->all();
    }

    private function pdfLogoDataUri(): string
    {
        $logoPath = public_path('assets/img/branding/phn-logo.svg');
        if (! is_file($logoPath)) {
            return '';
        }
        $content = file_get_contents($logoPath);
        if ($content === false) {
            return '';
        }
        $mime = 'image/svg+xml';

        return 'data:'.$mime.';base64,'.base64_encode($content);
    }

    private function sanitizeRoleSlugs(array $roleSlugs): array
    {
        $validSlugs = collect($this->roleDefinitions())
            ->map(fn (array $role): string => (string) ($role['slug'] ?? ''))
            ->filter(fn (string $slug): bool => $slug !== '')
            ->values()
            ->all();

        return collect($roleSlugs)
            ->map(fn ($slug): string => Str::lower(trim((string) $slug)))
            ->filter(fn (string $slug): bool => $slug !== '' && in_array($slug, $validSlugs, true))
            ->unique()
            ->values()
            ->all();
    }

    private function sanitizeRoleAssignmentContexts(array $contexts, array $allowedSlugs): array
    {
        return collect($contexts)
            ->filter(fn ($item): bool => is_array($item))
            ->map(function (array $item) use ($allowedSlugs): ?array {
                $roleSlug = Str::lower(trim((string) ($item['role_slug'] ?? '')));
                if ($roleSlug === '' || ! in_array($roleSlug, $allowedSlugs, true)) {
                    return null;
                }

                return [
                    'role_slug' => $roleSlug,
                    'time_start' => trim((string) ($item['time_start'] ?? '')),
                    'time_end' => trim((string) ($item['time_end'] ?? '')),
                    'project_scope' => trim((string) ($item['project_scope'] ?? '')),
                    'office_ip_only' => trim((string) ($item['office_ip_only'] ?? '')),
                ];
            })
            ->filter(fn ($item): bool => is_array($item))
            ->values()
            ->all();
    }

    private function departmentOptions(): array
    {
        return ['Production', 'Post', 'Finance', 'Creative', 'Operations', 'HR'];
    }

    private function employmentStatusOptions(): array
    {
        return ['full-time', 'freelance', 'intern'];
    }

    private function sendWelcomeEmail(
        User $user,
        string $temporaryPassword,
        string $activationLink,
        string $loginUrl,
        string $template,
        string $ccCsv,
        ?string $qrCodeUrl
    ): void {
        $username = Str::before($user->email, '@');
        $website = rtrim((string) config('app.url', url('/')), '/');
        if ($website === '') {
            $website = url('/');
        }
        $supportEmail = (string) config('mail.from.address', 'support@productionhousenusantara.com');
        $dateText = now()->translatedFormat('d F Y H:i');
        $body = str_replace(
            ['{{name}}', '{{username}}', '{{email}}', '{{password}}', '{{role}}', '{{date}}', '{{activation_link}}', '{{login_url}}', '{{website}}', '{{support_email}}'],
            [$user->name, $username, $user->email, $temporaryPassword, (string) ($user->primary_role ?? '-'), $dateText, $activationLink, $loginUrl, $website, $supportEmail],
            $template
        );
        if ($qrCodeUrl !== null && $qrCodeUrl !== '') {
            $body .= "\n\n2FA QR Code:\n".$qrCodeUrl;
        }

        $ccRecipients = collect(explode(',', $ccCsv))
            ->map(fn ($item): string => trim($item))
            ->filter(fn ($item): bool => $item !== '' && filter_var($item, FILTER_VALIDATE_EMAIL) !== false)
            ->values()
            ->all();

        Mail::raw($body, function ($message) use ($user, $ccRecipients): void {
            $message->to($user->email)
                ->subject('Welcome to PHN Dashboard');
            if (count($ccRecipients) > 0) {
                $message->cc($ccRecipients);
            }
        });
    }

    private function insertAuditLog(string $action, string $targetType, int $targetId, array $metadata = []): void
    {
        if (! Schema::hasTable('audit_logs')) {
            return;
        }
        DB::table('audit_logs')->insert([
            'actor_user_id' => Auth::id(),
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'metadata' => json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function autoSuspendExpiredUsers(): void
    {
        if (! Schema::hasColumn('users', 'auto_suspend_on_expiry') || ! Schema::hasColumn('users', 'suspended_at') || ! Schema::hasColumn('users', 'contract_end_at')) {
            return;
        }
        $expiredUsers = User::query()
            ->where('auto_suspend_on_expiry', true)
            ->whereNull('suspended_at')
            ->whereNotNull('contract_end_at')
            ->whereDate('contract_end_at', '<', now()->toDateString())
            ->get(['id', 'email']);

        foreach ($expiredUsers as $expiredUser) {
            $expiredUser->forceFill(['suspended_at' => now()])->save();
            $this->insertAuditLog('user.auto_suspended', 'user', $expiredUser->id, [
                'email' => $expiredUser->email,
                'reason' => 'contract_expired',
            ]);
        }
    }

    private function filterExistingUserColumns(array $attributes): array
    {
        $filtered = [];
        foreach ($attributes as $column => $value) {
            if (! Schema::hasColumn('users', (string) $column)) {
                continue;
            }
            $filtered[(string) $column] = $value;
        }

        return $filtered;
    }

    private function activeProjects(): array
    {
        $payloadRaw = (string) (LandingSetting::query()->where('key', 'cms_portfolio_payload')->value('value') ?? '');
        $payload = json_decode($payloadRaw, true);
        if (! is_array($payload)) {
            return [];
        }

        return collect($payload['projects'] ?? [])
            ->filter(fn ($item): bool => is_array($item))
            ->filter(fn (array $item): bool => ! (bool) ($item['is_draft'] ?? false))
            ->map(function (array $item): array {
                $title = trim((string) ($item['title'] ?? ''));
                $slug = Str::slug($title !== '' ? $title : (string) ($item['id'] ?? Str::random(6)));

                return [
                    'id' => $slug,
                    'title' => $title !== '' ? $title : 'Untitled Project',
                ];
            })
            ->values()
            ->all();
    }

    private function storeCroppedProfilePicture(?UploadedFile $file): string
    {
        if (! $file instanceof UploadedFile) {
            throw ValidationException::withMessages([
                'profile_picture' => 'Profile picture wajib diupload.',
            ]);
        }

        if (! function_exists('imagecreatefromstring') || ! function_exists('imagecopyresampled')) {
            $storedPath = $file->store('user-profiles', 'public');

            return Storage::url($storedPath);
        }

        $binary = file_get_contents($file->getRealPath());
        if ($binary === false) {
            throw ValidationException::withMessages([
                'profile_picture' => 'Gagal membaca file profile picture.',
            ]);
        }
        $source = @imagecreatefromstring($binary);
        if ($source === false) {
            throw ValidationException::withMessages([
                'profile_picture' => 'Format image profile picture tidak valid.',
            ]);
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $size = min($width, $height);
        $srcX = (int) floor(($width - $size) / 2);
        $srcY = (int) floor(($height - $size) / 2);
        $target = imagecreatetruecolor(480, 480);
        imagecopyresampled($target, $source, 0, 0, $srcX, $srcY, 480, 480, $size, $size);

        $filename = 'user-profiles/'.Str::uuid()->toString().'.jpg';
        $fullPath = Storage::disk('public')->path($filename);
        $directory = dirname($fullPath);
        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }
        imagejpeg($target, $fullPath, 90);

        return Storage::url($filename);
    }

    private function dispatchOptionalWebhookNotifications(User $user, string $activationLink): void
    {
        $payload = [
            'event' => 'user_onboarding',
            'email' => $user->email,
            'name' => $user->name,
            'activation_link' => $activationLink,
            'role' => $user->primary_role,
            'department' => $user->department,
        ];
        $slackWebhook = trim((string) env('SLACK_WEBHOOK_URL', ''));
        $whatsappWebhook = trim((string) env('WHATSAPP_WEBHOOK_URL', ''));
        if ($slackWebhook !== '') {
            Http::timeout(10)->post($slackWebhook, $payload);
        }
        if ($whatsappWebhook !== '') {
            Http::timeout(10)->post($whatsappWebhook, $payload);
        }
    }
}
