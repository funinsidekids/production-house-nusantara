<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TopicProgress extends Model
{
    protected $fillable = [
        'user_id', 'topic_id', 'total_attempts', 'correct_attempts',
        'accuracy', 'avg_time_seconds', 'mastery_status', 'last_activity_at',
    ];

    protected $casts = [
        'accuracy' => 'decimal:2',
        'last_activity_at' => 'datetime',
    ];

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
