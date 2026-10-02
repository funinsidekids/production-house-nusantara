<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModulMaterial extends Model
{
    protected $table = 'modul_materials';

    protected $fillable = [
        'chapter_id',
        'judul',
        'tipe',
        'isi',
        'perkiraan_menit',
        'nomor',
        'terbit',
    ];

    protected function casts(): array
    {
        return [
            'terbit' => 'boolean',
        ];
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(ModulChapter::class, 'chapter_id');
    }

    public function tipeLabel(): string
    {
        return match ($this->tipe) {
            'contoh' => 'Contoh Soal & Pembahasan',
            'latihan' => 'Latihan Mandiri',
            'rangkuman' => 'Rangkuman',
            'video' => 'Video Pembelajaran',
            default => 'Materi Teori',
        };
    }
}
