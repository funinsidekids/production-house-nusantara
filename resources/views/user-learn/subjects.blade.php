@extends('layouts.learn')
@section('title', 'Materi Belajar')
@section('content')
<h1 class="page">Mata Pelajaran Kelas XII</h1>
<p class="sub">Pilih mapel untuk melihat materi dan bank soal yang tersedia.</p>
<div class="grid c3">
    @foreach($subjects as $s)
        <div class="card" style="transition:.15s">
            <h3 style="color:#0b1f3a;font-size:15.5px">{{ $s->name }}</h3>
            <div style="font-size:12px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px;margin:2px 0 10px">{{ str_replace('_',' ',(string)$s->group) }}</div>
            <div style="display:flex;gap:14px;font-size:13px">
                <span>📖 <b>{{ (int)($counts['materials'][$s->id] ?? 0) }}</b> materi</span>
                <span>✏️ <b>{{ (int)($counts['questions'][$s->id] ?? 0) }}</b> soal</span>
            </div>
        </div>
    @endforeach
</div>
@endsection
