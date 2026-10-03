<x-modul-belajar.layout :title="$mapel->nama . ' — Modul Kelas 12'" :description="$mapel->deskripsi">
    <div class="container">
        <nav class="breadcrumb">
            <a href="{{ route('modul-belajar.index') }}">Modul Belajar</a> / <strong>{{ $mapel->nama }}</strong>
        </nav>

        <section class="section" style="padding-top:16px">
            <h2 style="display:flex;align-items:center;gap:12px">
                <span class="icon-sq" style="background:{{ $mapel->warna }};margin:0">{{ ['sigma' => '∑', 'atom' => '⚛', 'flask' => '🧪', 'leaf' => '🌿', 'globe' => '🌐'][$mapel->ikon] ?? '📘' }}</span>
                {{ $mapel->nama }}
            </h2>
            <p class="sub">{{ $mapel->deskripsi }}</p>

            @forelse ($chapters as $chapter)
                <article class="chapter">
                    <header>
                        <span class="num">Bab {{ $chapter->nomor }}</span>
                        <h3>{{ $chapter->judul }}</h3>
                        @if ($chapter->ringkasan)
                            <p>{{ $chapter->ringkasan }}</p>
                        @endif
                    </header>
                    <ul class="material-list">
                        @foreach ($chapter->materials as $material)
                            <li>
                                <a href="{{ route('modul-belajar.material', [$mapel->kode, $chapter->id, $material->id]) }}">
                                    <span class="tag tag-{{ $material->tipe }}">{{ $material->tipeLabel() }}</span>
                                    <span style="flex:1">{{ $material->judul }}</span>
                                    <span style="font-size:.78rem;color:#64748b">⏱ {{ $material->perkiraan_menit }} menit</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </article>
            @empty
                <div class="card"><p>Belum ada bab terbit untuk mata pelajaran ini.</p></div>
            @endforelse
        </section>
    </div>
</x-modul-belajar.layout>
