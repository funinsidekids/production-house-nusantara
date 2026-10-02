<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnswerAttempt extends Model
{
    public const SOURCES = ['practice', 'tryout', 'tka', 'remedial', 'ai_practice'];

    protected $fillable = [
        'user_id', 'tryout_session_id', 'question_id', 'topic_id', 'selected_option_key',
        'answer_text', 'is_correct', 'time_spent_seconds', 'source', 'client_request_id', 'answered_at',
    ];

    protected $casts = ['is_correct' => 'boolean', 'answered_at' => 'datetime'];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
