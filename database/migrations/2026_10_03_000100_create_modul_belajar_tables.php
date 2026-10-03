<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modul_mapels', function (Blueprint $table): void {
            $table->id();
            $table->string('kode', 16)->unique();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->string('ikon', 32)->default('book');
            $table->string('warna', 16)->default('#6366f1');
            $table->unsignedInteger('urutan')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('modul_chapters', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('mapel_id')->constrained('modul_mapels')->cascadeOnDelete();
            $table->string('judul');
            $table->text('ringkasan')->nullable();
            $table->unsignedInteger('nomor')->default(1);
            $table->boolean('terbit')->default(true);
            $table->timestamps();

            $table->unique(['mapel_id', 'nomor']);
        });

        Schema::create('modul_materials', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('chapter_id')->constrained('modul_chapters')->cascadeOnDelete();
            $table->string('judul');
            $table->enum('tipe', ['teori', 'contoh', 'latihan', 'rangkuman', 'video'])->default('teori');
            $table->longText('isi')->nullable();
            $table->unsignedInteger('perkiraan_menit')->default(15);
            $table->unsignedInteger('nomor')->default(1);
            $table->boolean('terbit')->default(true);
            $table->timestamps();
        });

        Schema::create('modul_progress', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('modul_materials')->cascadeOnDelete();
            $table->boolean('selesai')->default(false);
            $table->unsignedTinyInteger('skor_latihan')->nullable();
            $table->timestamp('diselesaikan_pada')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'material_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modul_progress');
        Schema::dropIfExists('modul_materials');
        Schema::dropIfExists('modul_chapters');
        Schema::dropIfExists('modul_mapels');
    }
};
