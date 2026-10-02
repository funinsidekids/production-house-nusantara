<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncLog extends Model
{
    protected $fillable = [
        'user_id', 'device_id', 'direction', 'changes_in', 'changes_out',
        'conflicts', 'report', 'idempotency_key',
    ];

    protected $casts = ['report' => 'array'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
