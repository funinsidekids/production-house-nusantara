<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TryoutSession extends Model
{
    protected $fillable = [
        'tryout_id', 'user_id', 'uuid', 'status', 'question_order', 'flagged_questions',
        'answered_count', 'started_at', 'expires_at', 'submitted_at', 'duration_seconds',
        'score', 'accuracy', 'statistics', 'submit_idempotency_key',
    ];

    protected $casts = [
        'question_order' => 'array',
        'flagged_questions' => 'array',
        'statistics' => 'array',
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'submitted_at' => 'datetime',
        'score' => 'decimal:2',
        'accuracy' => 'decimal:2',
    ];

    public function tryout(): BelongsTo
    {
        return $this->belongsTo(Tryout::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(TryoutAnswer::class);
    }

    public function isExpired(): bool
    {
        return $this->status === 'active'
            && $this->expires_at !== null
            && $this->expires_at->isPast();
    }
}
