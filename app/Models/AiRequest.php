<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiRequest extends Model
{
    protected $fillable = [
        'user_id', 'request_type', 'model', 'status', 'duration_ms', 'token_usage', 'error_message',
    ];

    protected $casts = ['token_usage' => 'array'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
