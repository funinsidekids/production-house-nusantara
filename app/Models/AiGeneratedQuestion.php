<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiGeneratedQuestion extends Model
{
    protected $fillable = [
        'question_id', 'user_id', 'ai_request_id', 'raw_payload', 'review_status',
        'validation_error', 'reviewed_by', 'reviewed_at',
    ];

    protected $casts = ['raw_payload' => 'array', 'reviewed_at' => 'datetime'];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
