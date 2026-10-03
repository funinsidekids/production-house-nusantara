<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_SUSPENDED = 'suspended';

    public const STATUS_DELETED = 'deleted';

    public const ROLE_SUPERADMIN = 'superadmin';

    public const ROLE_ADMIN = 'admin';

    public const ROLE_USER = 'user';

    public const APPROVAL_PENDING = 'pending';

    public const APPROVAL_APPROVED = 'approved';

    public const APPROVAL_REJECTED = 'rejected';

    protected $fillable = [
        'name',
        'email',
        'username',
        'phone',
        'status',
        'approval_status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'phone_verified_at',
        'last_login_at',
        'profile_picture_path',
        'password',
        'force_password_change',
        'two_factor_secret',
        'security_questions',
        'primary_role',
        'role_slugs',
        'role_assignment_contexts',
        'department',
        'assigned_projects',
        'employment_status',
        'contract_start_at',
        'contract_end_at',
        'auto_suspend_on_expiry',
        'suspended_at',
        'permissions_payload',
        'onboarded_by',
        'activation_token_hash',
        'activation_token_expires_at',
        'welcome_email_sent_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'security_questions' => 'array',
            'assigned_projects' => 'array',
            'permissions_payload' => 'array',
            'role_slugs' => 'array',
            'role_assignment_contexts' => 'array',
            'force_password_change' => 'boolean',
            'auto_suspend_on_expiry' => 'boolean',
            'contract_start_at' => 'date',
            'contract_end_at' => 'date',
            'suspended_at' => 'datetime',
            'activation_token_expires_at' => 'datetime',
            'welcome_email_sent_at' => 'datetime',
        ];
    }

    // ---- Learning OS relations & helpers ----

    public function studentProfile(): HasOne
    {
        return $this->hasOne(StudentProfile::class);
    }

    public function apiTokens(): HasMany
    {
        return $this->hasMany(ApiAccessToken::class);
    }

    public function isAdmin(): bool
    {
        return $this->isSuperAdmin() || $this->isRoleAdmin();
    }

    /**
     * Superadmin hanya dikenali dari role 'superadmin' (akun default disembunyikan dari daftar).
     */
    public function isSuperAdmin(): bool
    {
        return strtolower(trim((string) ($this->primary_role ?? ''))) === self::ROLE_SUPERADMIN;
    }

    public function isRoleAdmin(): bool
    {
        $role = strtolower(trim((string) ($this->primary_role ?? '')));

        if ($role === self::ROLE_ADMIN) {
            return true;
        }

        return in_array('admin', array_map('strtolower', (array) ($this->role_slugs ?? [])), true);
    }

    public function normalizedRole(): string
    {
        if ($this->isSuperAdmin()) {
            return self::ROLE_SUPERADMIN;
        }

        if ($this->isRoleAdmin()) {
            return self::ROLE_ADMIN;
        }

        return self::ROLE_USER;
    }

    public function isApproved(): bool
    {
        return strtolower((string) ($this->approval_status ?? self::APPROVAL_APPROVED)) === self::APPROVAL_APPROVED;
    }

    public function canLogin(): bool
    {
        return $this->isActiveAccount() && $this->isApproved();
    }

    public function isActiveAccount(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
