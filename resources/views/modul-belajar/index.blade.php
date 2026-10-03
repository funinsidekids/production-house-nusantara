<x-modul-belajar.layout title="Modul Belajar Kelas 12 SMA/MA">
    <section class="hero">
        <div class="container">
            <span class="badge-pill">✨ Kurikulum Kelas 12 SMA/MA — Siap UTBK/SNBT</span>
            <h1>Modul Belajar Online Kelas 12, Lengkap &amp; Terstruktur</h1>
            <p>Materi teori, contoh soal beserta pembahasan, latihan mandiri, rangkuman, dan video pembelajaran untuk semua mata pelajaran wajib maupun peminatan.</p>
        </div>
    </section>

    <section class="section" id="mapel">
        <div class="container">
            <h2>Pilih Mata Pelajaran</h2>
            <p class="sub">{{ $mapels->count() }} mata pelajaran tersedia dengan total {{ $mapels->sum('bab_terbit_count') }} bab.</p>

            <div class="grid">
                @foreach ($mapels as $mapel)
                    <a class="card" href="{{ route('modul-belajar.mapel', $mapel->kode) }}">
                        <span class="icon-sq" style="background:{{ $mapel->warna }}">{{ ['sigma' => '∑', 'atom' => '⚛', 'flask' => '🧪', 'leaf' => '🌿', 'globe' => '🌐'][$mapel->ikon] ?? '📘' }}</span>
                        <h3>{{ $mapel->nama }}</h3>
                        <p>{{ $mapel->deskripsi }}</p>
                        <div class="meta">
                            <span>📖 {{ $mapel->bab_terbit_count }} bab</span>
                            <span>🗂 {{ $mapel->chapters->sum('materi_terbit_count') }} materi</span>
                        </div>
                    </a>
                @endforeach
            </div>

            @if ($mapels->isEmpty())
                <div class="card">
                    <p>Belum ada mata pelajaran. Jalankan seeder:
                        <code>php artisan db:seed --class=Database\\Seeders\\ModulBelajarSeeder</code>
                    </p>
                </div>
            @endif
        </div>
    </section>
</x-modul-belajar.layout>
