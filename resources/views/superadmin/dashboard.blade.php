@extends('layouts.learn')
@section('title', 'Dashboard Superadmin')
@section('content')
<h1 class="page">Dashboard Superadmin</h1>
<p class="sub">Ringkasan sistem, persetujuan akun, dan aktivitas terbaru.</p>

<div class="grid c4">
    <div class="card stat"><div class="num">{{ $stats['users'] }}</div><div class="lbl">Total Akun (di luar superadmin)</div></div>
    <div class="card stat"><div class="num">{{ $stats['admins'] }}</div><div class="lbl">Admin</div></div>
    <div class="card stat" style="border:2px solid #d4a017"><div class="num" style="color:#ea580c">{{ $stats['pending'] }}</div><div class="lbl">Menunggu Persetujuan</div></div>
    <div class="card stat"><div class="num">{{ $stats['materials'] }}</div><div class="lbl">Materi</div></div>
    <div class="card stat"><div class="num">{{ $stats['subjects'] }}</div><div class="lbl">Mapel</div></div>
    <div class="card stat"><div class="num">{{ $stats['questions'] }}</div><div class="lbl">Bank Soal</div></div>
    <div class="card stat"><div class="num">{{ $stats['tryouts'] }}</div><div class="lbl">Tryout/TKA</div></div>
    <div class="card stat"><div class="num">{{ $stats['sessions'] }}</div><div class="lbl">Sesi Tryout</div></div>
</div>

<div class="grid c2" style="margin-top:22px">
    <div class="card">
        <h2 style="font-size:16px;color:#0b1f3a;margin-bottom:12px">⏳ Persetujuan Terbaru
            <a href="{{ route('superadmin.users.index') }}" style="float:right;font-size:12.5px;color:#d4a017;font-weight:700">Lihat semua →</a></h2>
        @forelse($pending as $u)
            <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid #eef2f7;gap:10px">
                <div>
                    <b>{{ $u->name }}</b> <span style="color:#64748b;font-size:12.5px">· {{ $u->email }}</span>
                    <div style="font-size:12px;color:#94a3b8">Daftar: {{ $u->created_at?->translatedFormat('d M Y H:i') }}</div>
                </div>
                <div style="display:flex;gap:6px">
                    <form method="POST" action="{{ route('superadmin.users.approve', $u->id) }}">@csrf<button class="btn sm ok">Setujui</button></form>
                    <form method="POST" action="{{ route('superadmin.users.reject', $u->id) }}">@csrf<button class="btn sm bad">Tolak</button></form>
                </div>
            </div>
        @empty
            <p style="color:#64748b;font-size:14px">Tidak ada akun menunggu persetujuan. 🎉</p>
        @endforelse
    </div>

    <div class="card">
        <h2 style="font-size:16px;color:#0b1f3a;margin-bottom:12px">📋 Audit Log Terakhir</h2>
        <table>
            <tr><th>Aksi</th><th>Keterangan</th><th>Waktu</th></tr>
            @forelse($audit as $log)
                <tr>
                    <td><span class="pill blue">{{ $log->action ?? $log->event ?? '-' }}</span></td>
                    <td style="font-size:12.5px">{{ Str::limit($log->description ?? '', 60) }}</td>
                    <td style="font-size:12px;color:#64748b">{{ $log->created_at ? \Carbon\Carbon::parse($log->created_at)->diffForHumans() : '' }}</td>
                </tr>
            @empty
                <tr><td colspan="3" style="color:#64748b">Belum ada log.</td></tr>
            @endforelse
        </table>
    </div>
</div>

<div class="card" style="margin-top:22px;display:flex;gap:12px;flex-wrap:wrap;align-items:center">
    <b>Aksi cepat:</b>
    <a class="btn" href="{{ route('superadmin.users.create') }}">+ Buat Akun Admin / User</a>
    <a class="btn dark" href="{{ route('superadmin.users.index') }}">Kelola Persetujuan Akun</a>
</div>
@endsection
