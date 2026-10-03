<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TryoutQuestion extends Model
{
    protected $fillable = ['tryout_id', 'question_id', 'position', 'points', 'is_compulsory'];

    protected $casts = ['is_compulsory' => 'boolean'];

    public function tryout(): BelongsTo
    {
        return $this->belongsTo(Tryout::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
