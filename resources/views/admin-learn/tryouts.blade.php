@extends('layouts.learn')
@section('title', 'Tryout & TKA')
@section('content')
<h1 class="page">Tryout &amp; TKA</h1>
<p class="sub">Ubah status paket tryout/TKA (draft → scheduled → open → closed → archived).</p>
<div class="card" style="overflow-x:auto">
<table>
    <tr><th>#</th><th>Judul</th><th>Mapel</th><th>Tipe</th><th>Durasi</th><th>Soal</th><th>Status</th></tr>
    @forelse($tryouts as $t)
        <tr>
            <td>{{ $t->id }}</td>
            <td>{{ Str::limit($t->title, 60) }}</td>
            <td style="font-size:12.5px">{{ $t->subject_name ?? 'Umum' }}</td>
            <td><span class="pill {{ $t->type==='tka'?'gold':'blue' }}">{{ strtoupper($t->type) }}</span></td>
            <td>{{ $t->duration_minutes }} mnt</td>
            <td>{{ $t->question_count }}</td>
            <td>
                <form method="POST" action="{{ route('admin.tryouts.status', $t->id) }}">@csrf
                    <select name="status" onchange="this.form.submit()" style="width:auto;padding:4px 8px;font-size:12px">
                        @foreach(['draft','scheduled','open','closed','archived'] as $st)
                            <option value="{{ $st }}" @selected($t->status===$st)>{{ $st }}</option>
                        @endforeach
                    </select>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="7" style="color:#64748b;text-align:center;padding:20px">Belum ada tryout.</td></tr>
    @endforelse
</table>
</div>
@endsection
