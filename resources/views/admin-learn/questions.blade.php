@extends('layouts.learn')
@section('title', 'Bank Soal')
@section('content')
<h1 class="page">Bank Soal</h1>
<p class="sub">200 soal terbaru — ubah status publish/archive langsung dari tabel.</p>
<div class="card" style="overflow-x:auto">
<table>
    <tr><th>#</th><th>Soal</th><th>Mapel</th><th>Tipe</th><th>Sulit</th><th>Status</th></tr>
    @forelse($questions as $q)
        <tr>
            <td>{{ $q->id }}</td>
            <td style="max-width:420px">{{ Str::limit(strip_tags($q->question_text), 90) }}</td>
            <td style="font-size:12.5px">{{ $q->subject_name ?? '—' }}</td>
            <td><span class="pill blue">{{ str_replace('_',' ',$q->type) }}</span></td>
            <td><span class="pill {{ $q->difficulty==='hard'?'red':($q->difficulty==='medium'?'orange':'green') }}">{{ $q->difficulty }}</span></td>
            <td>
                <form method="POST" action="{{ route('admin.questions.status', $q->id) }}">@csrf
                    <select name="status" onchange="this.form.submit()" style="width:auto;padding:4px 8px;font-size:12px">
                        @foreach(['draft','pending','validated','published','rejected','archived'] as $st)
                            <option value="{{ $st }}" @selected($q->status===$st)>{{ $st }}</option>
                        @endforeach
                    </select>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="6" style="color:#64748b;text-align:center;padding:20px">Belum ada soal.</td></tr>
    @endforelse
</table>
</div>
@endsection
