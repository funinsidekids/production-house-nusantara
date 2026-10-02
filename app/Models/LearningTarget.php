<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningTarget extends Model
{
    protected $fillable = [
        'user_id', 'subject_id', 'topic_id', 'title', 'description', 'status', 'target_date',
    ];

    protected $casts = ['target_date' => 'date'];

    public function tasks(): HasMany
    {
        return $this->hasMany(LearningTask::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
