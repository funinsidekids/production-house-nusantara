<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningTask extends Model
{
    protected $fillable = [
        'learning_target_id', 'user_id', 'title', 'kind', 'status', 'due_date', 'completed_at',
    ];

    protected $casts = ['due_date' => 'date', 'completed_at' => 'datetime'];

    public function target(): BelongsTo
    {
        return $this->belongsTo(LearningTarget::class, 'learning_target_id');
    }
}
