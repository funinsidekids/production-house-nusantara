<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Topic extends Model
{
    protected $fillable = ['chapter_id', 'title', 'description', 'position', 'status'];

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function progressRecords(): HasMany
    {
        return $this->hasMany(TopicProgress::class);
    }
}
