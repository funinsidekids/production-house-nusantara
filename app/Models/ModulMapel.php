<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModulMapel extends Model
{
    protected $table = 'modul_mapels';

    protected $fillable = [
        'kode',
        'nama',
        'deskripsi',
        'ikon',
        'warna',
        'urutan',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
        ];
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(ModulChapter::class, 'mapel_id')->orderBy('nomor');
    }

    public function scopeAktif($query)
    {
        return $query->where('aktif', true)->orderBy('urutan');
    }
}
