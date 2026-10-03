@extends('layouts.learn')
@section('title', 'Dashboard Belajar')
@section('content')
<h1 class="page">Halo, {{ Str::before(auth()->user()->name, ' ') }} 👋</h1>
<p class="sub">Ringkasan progres belajarmu hari ini.</p>

<div class="grid c4">
    <div class="card stat"><div class="num">{{ $stats['answered'] }}</div><div class="lbl">Soal Dijawab</div></div>
    <div class="card stat"><div class="num" style="color:#16a34a">{{ $stats['accuracy'] }}%</div><div class="lbl">Akurasi</div></div>
    <div class="card stat"><div class="num">{{ $stats['tryouts_done'] }}</div><div class="lbl">Tryout Selesai</div></div>
    <div class="card stat"><div class="num">{{ $stats['tryout_best'] ?: '—' }}</div><div class="lbl">Skor Terbaik TKA/Tryout</div></div>
    <div class="card stat"><div class="num">{{ $stats['bookmarks'] }}</div><div class="lbl">Bookmark</div></div>
    <div class="card stat"><div class="num">{{ $stats['notes'] }}</div><div class="lbl">Catatan</div></div>
</div>

<div class="grid c2" style="margin-top:22px">
    <div class="card">
        <h2 style="font-size:15px;color:#0b1f3a;margin-bottom:10px">📊 Performa per Mapel</h2>
        @forelse($stats['by_subject'] as $row)
            <div style="margin-bottom:10px">
                <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px"><span>{{ $row['name'] }}</span><b>{{ $row['accuracy'] }}% <span style="color:#94a3b8;font-weight:400">({{ $row['total'] }} soal)</span></b></div>
                <div class="progress"><i style="width:{{ $row['accuracy'] }}%"></i></div>
            </div>
        @empty
            <p style="color:#64748b;font-size:14px">Belum ada data. Mulai kerjakan latihan soal ya!</p>
        @endforelse
    </div>

    <div class="card">
        <h2 style="font-size:15px;color:#0b1f3a;margin-bottom:10px">🕐 Jawaban Terakhir</h2>
        <table>
            <tr><th>Soal</th><th>Mapel</th><th>Hasil</th></tr>
            @forelse($recentAttempts as $a)
                <tr>
                    <td style="max-width:260px;font-size:12.5px">{{ Str::limit(strip_tags((string)$a->question_text), 55) }}</td>
                    <td style="font-size:12px">{{ $a->subject_name ?? '—' }}</td>
                    <td><span class="pill {{ $a->is_correct?'green':'red' }}">{{ $a->is_correct?'✓ benar':'✗ salah' }}</span></td>
                </tr>
            @empty
                <tr><td colspan="3" style="color:#64748b">Belum ada aktivitas.</td></tr>
            @endforelse
        </table>
    </div>
</div>

<div class="card" style="margin-top:22px">
    <h2 style="font-size:15px;color:#0b1f3a;margin-bottom:10px">🎯 Target Belajar Aktif</h2>
    @forelse($targets as $t)
        <div style="padding:10px 0;border-bottom:1px solid #eef2f7">
            <b>{{ $t->title }}</b> <span class="pill gold">{{ $t->status }}</span>
            @if($t->tasks->isNotEmpty())
                <div style="font-size:12.5px;color:#64748b;margin-top:4px">
                    Tasks: {{ $t->tasks->pluck('title')->join(', ') }}
                </div>
            @endif
        </div>
    @empty
        <p style="color:#64748b;font-size:14px">Belum ada target belajar aktif.</p>
    @endforelse
</div>

<div class="card" style="margin-top:22px;display:flex;gap:10px;flex-wrap:wrap">
    <a class="btn" href="{{ route('user.subjects') }}">📚 Mulai Belajar</a>
    <a class="btn dark" href="{{ route('user.tryouts') }}">📝 Tryout &amp; TKA</a>
    <a class="btn ghost" href="{{ route('user.profile') }}">⚙️ Lengkapi Profil</a>
</div>
@endsection
