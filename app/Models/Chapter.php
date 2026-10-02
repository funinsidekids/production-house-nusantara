<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chapter extends Model
{
    protected $fillable = ['curriculum_subject_id', 'number', 'title', 'summary', 'status'];

    public function curriculumSubject(): BelongsTo
    {
        return $this->belongsTo(CurriculumSubject::class);
    }

    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class)->orderBy('position');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
