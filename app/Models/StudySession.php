<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudySession extends Model
{
    protected $fillable = [
        'user_id', 'subject_id', 'topic_id', 'duration_seconds',
        'questions_answered', 'questions_correct', 'day',
    ];

    protected $casts = ['day' => 'date'];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
