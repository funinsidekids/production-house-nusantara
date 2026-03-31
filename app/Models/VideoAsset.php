<?php

namespace App\Models;

use App\Jobs\ConvertVideoJob;
use Illuminate\Database\Eloquent\Model;

class VideoAsset extends Model
{
    protected $fillable = [
        'title',
        'context',
        'source_path',
        'webm_path',
        'mp4_path',
        'thumb_path',
        'status',
        'error_message',
        'stream_path',
        'cdn_url',
        'duration_seconds',
        'converted_at',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'converted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::created(function (VideoAsset $asset): void {
            if ($asset->status === 'uploaded') {
                ConvertVideoJob::dispatch($asset->id)->onQueue('media');
            }
        });
    }
}
