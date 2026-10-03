@extends('layouts.learn')
@section('title', 'Data Siswa')
@section('content')
<h1 class="page">Data Siswa</h1>
<p class="sub">300 siswa terakhir (role user). Manajemen akun penuh dilakukan Superadmin.</p>
<div class="card" style="overflow-x:auto">
<table>
    <tr><th>Nama</th><th>Email</th><th>Username</th><th>Status</th><th>Persetujuan</th><th>Daftar</th></tr>
    @forelse($students as $s)
        <tr>
            <td><b>{{ $s->name }}</b></td>
            <td style="font-size:12.5px">{{ $s->email }}</td>
            <td style="font-size:12.5px">@{{ $s->username }}</td>
            <td><span class="pill {{ $s->status==='active'?'green':'orange' }}">{{ $s->status }}</span></td>
            <td><span class="pill {{ $s->approval_status==='approved'?'green':($s->approval_status==='pending'?'gold':'red') }}">{{ $s->approval_status }}</span></td>
            <td style="font-size:12px;color:#64748b">{{ \Carbon\Carbon::parse($s->created_at)->format('d M Y') }}</td>
        </tr>
    @empty
        <tr><td colspan="6" style="color:#64748b;text-align:center;padding:20px">Belum ada siswa.</td></tr>
    @endforelse
</table>
</div>
@endsection
