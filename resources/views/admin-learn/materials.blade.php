@extends('layouts.learn')
@section('title', 'Materi')
@section('content')
<h1 class="page">Materi Pembelajaran</h1>
<p class="sub">200 materi terbaru. Konten bersumber dari database (server authoritative).</p>
<div class="card" style="overflow-x:auto">
<table>
    <tr><th>Judul</th><th>Mapel</th><th>Tipe</th><th>Kelas</th><th>Status</th><th>Update</th></tr>
    @forelse($materials as $m)
        <tr>
            <td>{{ Str::limit($m->title, 70) }}</td>
            <td style="font-size:12.5px">{{ $m->subject_name ?? '—' }}</td>
            <td><span class="pill blue">{{ $m->type }}</span></td>
            <td>{{ $m->grade }}</td>
            <td><span class="pill {{ $m->status==='published'?'green':'gold' }}">{{ $m->status }}</span></td>
            <td style="font-size:12px;color:#64748b">{{ \Carbon\Carbon::parse($m->updated_at)->diffForHumans() }}</td>
        </tr>
    @empty
        <tr><td colspan="6" style="color:#64748b;text-align:center;padding:20px">Belum ada materi.</td></tr>
    @endforelse
</table>
</div>
@endsection
