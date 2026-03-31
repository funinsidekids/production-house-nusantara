<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
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
}
