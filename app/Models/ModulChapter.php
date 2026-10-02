<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModulChapter extends Model
{
    protected $table = 'modul_chapters';

    protected $fillable = [
        'mapel_id',
        'judul',
        'ringkasan',
        'nomor',
        'terbit',
    ];

    protected function casts(): array
    {
        return [
            'terbit' => 'boolean',
        ];
    }

    public function mapel(): BelongsTo
    {
        return $this->belongsTo(ModulMapel::class, 'mapel_id');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(ModulMaterial::class, 'chapter_id')->orderBy('nomor');
    }
}
