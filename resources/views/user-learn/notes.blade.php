@extends('layouts.learn')
@section('title', 'Catatan')
@section('content')
<h1 class="page">Catatan Belajar</h1>
<p class="sub">Catatan privat kamu — hanya kamu dan Superadmin yang dapat mengaksesnya.</p>
<div class="grid c2">
    <div class="card">
        <h2 style="font-size:15px;color:#0b1f3a;margin-bottom:6px">+ Catatan Baru</h2>
        <form method="POST" action="{{ route('user.notes.store') }}">
            @csrf
            <label>Judul (opsional)</label>
            <input name="title" maxlength="150">
            <label>Isi Catatan</label>
            <textarea name="body" rows="6" required maxlength="5000"></textarea>
            <button class="btn" style="margin-top:14px" type="submit">Simpan Catatan</button>
        </form>
    </div>
    <div class="card">
        <h2 style="font-size:15px;color:#0b1f3a;margin-bottom:10px">Daftar Catatan</h2>
        @forelse($notes as $n)
            <div style="padding:10px 0;border-bottom:1px solid #eef2f7;display:flex;justify-content:space-between;gap:10px">
                <div>
                    <b style="font-size:14px">{{ $n->title ?: '(tanpa judul)' }}</b>
                    <div style="font-size:13px;color:#475569;white-space:pre-line">{{ Str::limit($n->body, 140) }}</div>
                    <div style="font-size:11.5px;color:#94a3b8">{{ $n->created_at?->diffForHumans() }}</div>
                </div>
                <form method="POST" action="{{ route('user.notes.destroy', $n->id) }}" onsubmit="return confirm('Hapus catatan?')">
                    @csrf @method('DELETE')
                    <button class="btn sm bad">Hapus</button>
                </form>
            </div>
        @empty
            <p style="color:#64748b;font-size:14px">Belum ada catatan.</p>
        @endforelse
        {{ $notes->links() }}
    </div>
</div>
@endsection
