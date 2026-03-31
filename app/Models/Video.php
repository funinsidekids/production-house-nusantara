<?php

namespace App\Models;

class Video extends VideoAsset
{
    protected $table = 'video_assets';

    protected $appends = [
        'file',
        'webm',
        'mp4',
        'thumb',
        'duration',
    ];

    public function getFileAttribute(): ?string
    {
        return $this->source_path;
    }

    public function setFileAttribute(?string $value): void
    {
        $this->attributes['source_path'] = $value;
    }

    public function getWebmAttribute(): ?string
    {
        return $this->webm_path;
    }

    public function setWebmAttribute(?string $value): void
    {
        $this->attributes['webm_path'] = $value;
    }

    public function getMp4Attribute(): ?string
    {
        return $this->mp4_path;
    }

    public function setMp4Attribute(?string $value): void
    {
        $this->attributes['mp4_path'] = $value;
    }

    public function getThumbAttribute(): ?string
    {
        return $this->thumb_path;
    }

    public function setThumbAttribute(?string $value): void
    {
        $this->attributes['thumb_path'] = $value;
    }

    public function getDurationAttribute(): ?int
    {
        return $this->duration_seconds;
    }

    public function setDurationAttribute(?int $value): void
    {
        $this->attributes['duration_seconds'] = $value;
    }
}
