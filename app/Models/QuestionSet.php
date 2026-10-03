<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionSet extends Model
{
    protected $fillable = [
        'title', 'description', 'subject_id', 'grade', 'purpose',
        'status', 'created_by', 'duration_minutes',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuestionSetQuestion::class)->orderBy('position');
    }

    public function tryouts(): HasMany
    {
        return $this->hasMany(Tryout::class);
    }
}
