@extends('layouts.learn')
@section('title', 'Mata Pelajaran')
@section('content')
<h1 class="page">Mata Pelajaran</h1>
<p class="sub">Daftar mapel bersumber dari database — tambahkan mapel baru di sini.</p>

<div class="grid c2">
    <div class="card">
        <h2 style="font-size:15px;color:#0b1f3a;margin-bottom:6px">+ Tambah Mapel</h2>
        <form method="POST" action="{{ route('admin.subjects.store') }}">
            @csrf
            <label>Nama Mapel</label>
            <input name="name" required maxlength="120" placeholder="cth: Matematika Tingkat Lanjut">
            <label>Kode</label>
            <input name="code" required maxlength="20" placeholder="cth: MTL (unik, huruf kecil)">
            <label>Kelompok</label>
            <select name="group" required>
                <option value="umum">Umum</option>
                <option value="peminatan">Peminatan</option>
                <option value="keagamaan">Keagamaan (MA)</option>
                <option value="muatan_lokal">Muatan Lokal</option>
            </select>
            <button class="btn" style="margin-top:16px" type="submit">Simpan Mapel</button>
        </form>
    </div>

    <div class="card">
        <h2 style="font-size:15px;color:#0b1f3a;margin-bottom:10px">Daftar Mapel ({{ $subjects->count() }})</h2>
        <div style="overflow-x:auto;max-height:480px">
        <table>
            <tr><th>Nama</th><th>Kode</th><th>Kelompok</th><th>Status</th></tr>
            @foreach($subjects as $s)
                <tr>
                    <td>{{ $s->name }}</td>
                    <td><code style="font-size:12px">{{ $s->code }}</code></td>
                    <td><span class="pill blue">{{ str_replace('_',' ',$s->group) }}</span></td>
                    <td><span class="pill {{ $s->active?'green':'gray' }}">{{ $s->active?'aktif':'nonaktif' }}</span></td>
                </tr>
            @endforeach
        </table>
        </div>
    </div>
</div>
@endsection
