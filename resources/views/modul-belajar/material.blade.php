<x-modul-belajar.layout :title="$material->judul . ' — ' . $mapel->nama" :description="Str::limit(strip_tags($material->isi), 150)">
    <div class="container">
        <nav class="breadcrumb">
            <a href="{{ route('modul-belajar.index') }}">Modul Belajar</a> /
            <a href="{{ route('modul-belajar.mapel', $mapel->kode) }}">{{ $mapel->nama }}</a> /
            <strong>Bab {{ $chapter->nomor }}</strong>
        </nav>

        <article class="article" style="margin-top:14px">
            <span class="tag tag-{{ $material->tipe }}">{{ $material->tipeLabel() }}</span>
            <h1>{{ $material->judul }}</h1>
            <p class="lead">Bab {{ $chapter->nomor }}: {{ $chapter->judul }} · ⏱ Estimasi belajar {{ $material->perkiraan_menit }} menit</p>
            <div class="content">{{ $material->isi }}</div>

            <div style="margin-top:26px;display:flex;gap:12px;align-items:center;flex-wrap:wrap">
                <button class="btn btn-primary" id="btnSelesai"
                        data-url="{{ route('modul-belajar.progress', $material->id) }}"
                        data-csrf="{{ csrf_token() }}">
                    ✅ Tandai Sudah Dipelajari
                </button>
                <span id="statusProgres" style="font-size:.85rem;color:#64748b"></span>
            </div>
        </article>

        <div class="nav-row">
            @if ($prevNext['prev'])
                <a class="card" href="{{ route('modul-belajar.material', [$mapel->kode, $prevNext['prev']['chapter_id'], $prevNext['prev']['id']]) }}">
                    <span class="small">← Sebelumnya</span>
                    <h3 style="font-size:.95rem;margin-top:4px">{{ $prevNext['prev']['judul'] }}</h3>
                </a>
            @else
                <span></span>
            @endif

            @if ($prevNext['next'])
                <a class="card" style="text-align:right" href="{{ route('modul-belajar.material', [$mapel->kode, $prevNext['next']['chapter_id'], $prevNext['next']['id']]) }}">
                    <span class="small">Berikutnya →</span>
                    <h3 style="font-size:.95rem;margin-top:4px">{{ $prevNext['next']['judul'] }}</h3>
                </a>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        (function () {
            const btn = document.getElementById('btnSelesai');
            const status = document.getElementById('statusProgres');
            const toast = document.getElementById('toast');
            let selesai = false;

            function showToast(msg) {
                toast.textContent = msg;
                toast.classList.add('show');
                setTimeout(() => toast.classList.remove('show'), 2600);
            }

            btn.addEventListener('click', async () => {
                btn.disabled = true;
                try {
                    const res = await fetch(btn.dataset.url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': btn.dataset.csrf,
                        },
                        body: JSON.stringify({ selesai: !selesai }),
                    });
                    const json = await res.json();
                    if (res.ok && json.sukses) {
                        selesai = json.data.selesai;
                        btn.textContent = selesai ? '✔ Sudah Dipelajari' : '✅ Tandai Sudah Dipelajari';
                        status.textContent = selesai ? 'Materi ini tercatat selesai.' : '';
                        showToast(json.message);
                    } else if (res.status === 401) {
                        showToast('Login dulu untuk menyimpan progres.');
                    } else {
                        showToast('Gagal menyimpan progres.');
                    }
                } catch (e) {
                    showToast('Koneksi bermasalah, coba lagi.');
                } finally {
                    btn.disabled = false;
                }
            });
        })();
    </script>
    @endpush
</x-modul-belajar.layout>
