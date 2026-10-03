@extends('layouts.learn')
@section('title', 'Moderasi Soal AI')
@section('content')
<h1 class="page">Moderasi Soal AI Generated</h1>
<p class="sub">Alur: AI Generated → Pending → Review Admin → Approved/Published atau Rejected. Soal AI tidak otomatis menjadi konten resmi.</p>
<div class="card" style="overflow-x:auto">
<table>
    <tr><th>#</th><th>Pertanyaan</th><th>Subjek/Topik</th><th>Diff</th><th>Review</th><th>Aksi</th></tr>
    @forelse($rows as $r)
        <tr>
            <td>{{ $r->id }}</td>
            <td style="max-width:380px;font-size:12.5px">{{ Str::limit((string)($r->question_text ?? json_decode((string)($r->question_json ?? ''))->question ?? '(lihat JSON)'), 100) }}</td>
            <td style="font-size:12px">{{ $r->subject_id ? 'ID '.$r->subject_id : '—' }}{{ $r->topic_id ? ' · topik '.$r->topic_id : '' }}</td>
            <td><span class="pill orange">{{ $r->difficulty ?? '—' }}</span></td>
            <td><span class="pill {{ ($r->review_status ?? 'pending')==='approved'?'green':(($r->review_status ?? 'pending')==='rejected'?'red':'gold') }}">{{ $r->review_status ?? 'pending' }}</span></td>
            <td>
                @if(($r->review_status ?? 'pending') === 'pending')
                <div style="display:flex;gap:5px">
                    <form method="POST" action="{{ route('admin.ai.review', ['id'=>$r->id,'decision'=>'approve']) }}">@csrf<button class="btn sm ok">Setujui &amp; Publish</button></form>
                    <form method="POST" action="{{ route('admin.ai.review', ['id'=>$r->id,'decision'=>'reject']) }}">@csrf<button class="btn sm bad">Tolak</button></form>
                </div>
                @else
                    <span style="font-size:12px;color:#64748b">Selesai review</span>
                @endif
            </td>
        </tr>
    @empty
        <tr><td colspan="6" style="color:#64748b;text-align:center;padding:20px">Belum ada soal hasil AI.</td></tr>
    @endforelse
</table>
</div>
@endsection
