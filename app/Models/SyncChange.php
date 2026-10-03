<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncChange extends Model
{
    protected $fillable = [
        'user_id', 'entity', 'entity_id', 'local_id', 'operation',
        'content_hash', 'payload', 'changed_at',
    ];

    protected $casts = ['payload' => 'array', 'changed_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
