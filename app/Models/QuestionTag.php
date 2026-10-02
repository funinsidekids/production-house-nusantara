<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionTag extends Model
{
    protected $fillable = ['name', 'slug'];

    public function relations(): HasMany
    {
        return $this->hasMany(QuestionTagRelation::class, 'tag_id');
    }
}
