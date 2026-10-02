<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    protected $fillable = ['name', 'npsn', 'city', 'province', 'type'];

    public const TYPES = ['SMA', 'MA', 'SMK', 'MAN', 'other'];

    public function classes(): HasMany
    {
        return $this->hasMany(SchoolClass::class);
    }

    public function studentProfiles(): HasMany
    {
        return $this->hasMany(StudentProfile::class);
    }
}
