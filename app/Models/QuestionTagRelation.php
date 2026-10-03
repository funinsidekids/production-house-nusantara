<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionTagRelation extends Model
{
    protected $fillable = ['question_id', 'tag_id'];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(QuestionTag::class, 'tag_id');
    }
}
