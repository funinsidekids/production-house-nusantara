<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Backup extends Model
{
    protected $fillable = [
        'uuid', 'user_id', 'device_id', 'name', 'size_bytes', 'storage_path',
        'checksum_sha256', 'status', 'record_count', 'idempotency_key',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
