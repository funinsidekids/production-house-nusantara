@extends('layouts.learn')
@section('title', 'Bookmark')
@section('content')
<h1 class="page">Bookmark Saya</h1>
<p class="sub">Materi, soal, dan topik yang kamu tandai.</p>
<div class="card">
<table>
    <tr><th>Tipe</th><th>Item</th><th>Ditandai</th></tr>
    @forelse($bookmarks as $b)
        <tr>
            <td><span class="pill blue">{{ $b->type }}</span></td>
            <td>{{ Str::limit((string)$b->label, 90) }}</td>
            <td style="font-size:12.5px;color:#64748b">{{ \Carbon\Carbon::parse($b->created_at)->diffForHumans() }}</td>
        </tr>
    @empty
        <tr><td colspan="3" style="color:#64748b;text-align:center;padding:20px">Belum ada bookmark.</td></tr>
    @endforelse
</table>
{{ $bookmarks->links() }}
</div>
@endsection
