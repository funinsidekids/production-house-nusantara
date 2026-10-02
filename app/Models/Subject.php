<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $fillable = ['name', 'code', 'icon', 'color', 'group', 'active', 'sort_order'];

    protected $casts = ['active' => 'boolean'];

    public function curriculumSubjects(): HasMany
    {
        return $this->hasMany(CurriculumSubject::class);
    }

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function chapters(): HasMany
    {
        return $this->hasManyThrough(Chapter::class, CurriculumSubject::class);
    }
}
