<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TryoutAnswer extends Model
{
    protected $fillable = [
        'tryout_session_id', 'question_id', 'selected_option_key', 'answer_text',
        'is_correct', 'time_spent_seconds', 'flagged', 'attempt_number', 'client_request_id',
    ];

    protected $casts = ['is_correct' => 'boolean', 'flagged' => 'boolean'];

    public function session(): BelongsTo
    {
        return $this->belongsTo(TryoutSession::class, 'tryout_session_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
