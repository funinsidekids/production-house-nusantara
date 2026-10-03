<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tryout extends Model
{
    protected $fillable = [
        'title', 'description', 'subject_id', 'question_set_id', 'type', 'grade',
        'duration_minutes', 'question_count', 'question_order', 'status',
        'starts_at', 'ends_at', 'auto_publish_result', 'created_by',
    ];

    protected $casts = [
        'question_order' => 'array',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'auto_publish_result' => 'boolean',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function questionSet(): BelongsTo
    {
        return $this->belongsTo(QuestionSet::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TryoutQuestion::class)->orderBy('position');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(TryoutSession::class);
    }

    public function isTka(): bool
    {
        return $this->type === 'tka';
    }
}
