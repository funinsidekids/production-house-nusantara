@extends('layouts.learn')
@section('title', 'Dashboard Admin')
@section('content')
<h1 class="page">Dashboard Admin</h1>
<p class="sub">Pantau konten pembelajaran, aktivitas tryout, dan moderasi soal AI.</p>

<div class="grid c4">
    <div class="card stat"><div class="num">{{ $stats['students'] }}</div><div class="lbl">Siswa Terdaftar</div></div>
    <div class="card stat"><div class="num" style="color:#16a34a">{{ $stats['active_students'] }}</div><div class="lbl">Siswa Aktif</div></div>
    <div class="card stat"><div class="num">{{ $stats['subjects'] }}</div><div class="lbl">Mapel Aktif</div></div>
    <div class="card stat"><div class="num">{{ $stats['materials'] }}</div><div class="lbl">Materi Published</div></div>
    <div class="card stat"><div class="num">{{ $stats['questions'] }}</div><div class="lbl">Soal Published</div></div>
    <div class="card stat"><div class="num" style="color:#ea580c">{{ $stats['draft_questions'] }}</div><div class="lbl">Soal Draft</div></div>
    <div class="card stat"><div class="num">{{ $stats['tryouts'] }}</div><div class="lbl">Tryout/TKA Aktif</div></div>
    <div class="card stat"><div class="num">{{ $stats['sessions'] }}</div><div class="lbl">Total Sesi Tryout</div></div>
    <div class="card stat"><div class="num">{{ $stats['ai_generated'] }}</div><div class="lbl">Soal AI Generated</div></div>
    <div class="card stat" style="border:2px solid #d4a017"><div class="num" style="color:#ea580c">{{ $stats['ai_pending'] }}</div><div class="lbl">Moderasi AI Pending</div></div>
</div>

<div class="grid c2" style="margin-top:22px">
    <div class="card">
        <h2 style="font-size:16px;color:#0b1f3a;margin-bottom:10px">🕒 Sesi Tryout Terbaru
            <a href="{{ route('admin.tryouts') }}" style="float:right;font-size:12.5px;color:#d4a017;font-weight:700">Kelola Tryout →</a></h2>
        <table>
            <tr><th>Siswa</th><th>Tryout</th><th>Status</th><th>Skor</th></tr>
            @forelse($recentSessions as $s)
                <tr>
                    <td>{{ $s->user_name }}</td>
                    <td style="font-size:12.5px">{{ $s->tryout_title }}</td>
                    <td><span class="pill {{ $s->status==='completed'?'green':($s->status==='active'?'blue':'gray') }}">{{ $s->status }}</span></td>
                    <td>{{ $s->score !== null ? round($s->score,1) : '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="4" style="color:#64748b">Belum ada sesi tryout.</td></tr>
            @endforelse
        </table>
    </div>

    <div class="card">
        <h2 style="font-size:16px;color:#0b1f3a;margin-bottom:10px">🏆 Siswa Paling Aktif</h2>
        <table>
            <tr><th>Nama</th><th>Dijawab</th><th>Akurasi</th></tr>
            @forelse($topStudents as $t)
                <tr>
                    <td>{{ $t->name }}</td>
                    <td>{{ $t->total }}</td>
                    <td style="width:45%">
                        <div style="display:flex;align-items:center;gap:8px">
                            <div class="progress" style="flex:1"><i style="width:{{ $t->accuracy }}%"></i></div>
                            <b style="font-size:12px">{{ $t->accuracy }}%</b>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" style="color:#64748b">Belum ada aktivitas jawaban.</td></tr>
            @endforelse
        </table>
    </div>
</div>

<div class="card" style="margin-top:22px;display:flex;gap:10px;flex-wrap:wrap">
    <a class="btn" href="{{ route('admin.subjects') }}">Kelola Mapel</a>
    <a class="btn dark" href="{{ route('admin.materials') }}">Kelola Materi</a>
    <a class="btn dark" href="{{ route('admin.questions') }}">Bank Soal</a>
    <a class="btn dark" href="{{ route('admin.tryouts') }}">Tryout / TKA</a>
    <a class="btn" href="{{ route('admin.ai') }}">✳ Moderasi Soal AI ({{ $stats['ai_pending'] }})</a>
    <a class="btn ghost" href="{{ route('admin.students') }}">Data Siswa</a>
</div>
@endsection
