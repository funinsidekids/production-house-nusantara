<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Curriculum extends Model
{
    protected $table = 'curriculums';

    protected $fillable = ['name', 'code', 'version', 'is_active', 'description'];

    protected $casts = ['is_active' => 'boolean'];

    public function curriculumSubjects(): HasMany
    {
        return $this->hasMany(CurriculumSubject::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public static function active(): ?self
    {
        return self::where('is_active', true)->orderByDesc('version')->first();
    }
}
