@extends('layouts.learn')
@section('title', 'Tryout & TKA')
@section('content')
<h1 class="page">Tryout &amp; TKA</h1>
<p class="sub">Paket ujian yang tersedia (type TKA ditandai emas). Sesi tryout juga dapat dijalankan dari aplikasi Android melalui API.</p>
<div class="grid c3">
    @forelse($tryouts as $t)
        <div class="card" style="{{ $t->type==='tka'?'border:2px solid #d4a017':'' }}">
            <span class="pill {{ $t->type==='tka'?'gold':'blue' }}">{{ strtoupper($t->type) }}</span>
            <span class="pill {{ $t->status==='open'?'green':'gray' }}">{{ $t->status }}</span>
            <h3 style="color:#0b1f3a;font-size:15px;margin:8px 0 4px">{{ $t->title }}</h3>
            <div style="font-size:12.5px;color:#64748b">
                {{ $t->subject_name ?? 'Semua mapel' }} · Kelas {{ $t->grade }}<br>
                ⏱ {{ $t->duration_minutes }} menit · {{ $t->question_count }} soal
            </div>
        </div>
    @empty
        <div class="card" style="grid-column:1/-1;color:#64748b">Belum ada tryout yang dibuka.</div>
    @endforelse
</div>

@if($sessions->isNotEmpty())
<div class="card" style="margin-top:22px">
    <h2 style="font-size:15px;color:#0b1f3a;margin-bottom:10px">📈 Riwayat Sesi Saya</h2>
    <table>
        <tr><th>Tryout</th><th>Mulai</th><th>Status</th><th>Skor</th></tr>
        @foreach($sessions as $s)
            <tr>
                <td>{{ optional($s->tryout)->title ?? '—' }}</td>
                <td style="font-size:12.5px">{{ $s->started_at?->format('d M Y H:i') }}</td>
                <td><span class="pill {{ $s->status==='completed'?'green':($s->status==='active'?'blue':'gray') }}">{{ $s->status }}</span></td>
                <td><b>{{ $s->score !== null ? round($s->score,1) : '—' }}</b></td>
            </tr>
        @endforeach
    </table>
</div>
@endif
@endsection
