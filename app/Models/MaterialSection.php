<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialSection extends Model
{
    protected $fillable = ['material_id', 'heading', 'body', 'kind', 'position'];

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }
}
