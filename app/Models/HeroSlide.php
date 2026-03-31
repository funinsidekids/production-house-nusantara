<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeroSlide extends Model
{
    protected $fillable = [
        'title',
        'caption',
        'video_url',
        'cta_text',
        'cta_url',
        'sort_order',
        'duration_seconds',
        'overlay_opacity',
        'is_active',
    ];
}
