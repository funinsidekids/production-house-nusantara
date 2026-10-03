@extends('layouts.learn')
@section('title', 'Profil')
@section('content')
<h1 class="page">Profil Siswa</h1>
<p class="sub">Lengkapi data sekolah & kelas untuk pengalaman belajar yang lebih tepat.</p>
<div style="max-width:520px">
<div class="card">
    <form method="POST" action="{{ route('user.profile.update') }}">
        @csrf @method('PUT')
        <label>Nama Lengkap</label>
        <input name="name" value="{{ old('name', $user->name) }}" required maxlength="120">
        <label>Sekolah</label>
        <select name="school_id">
            <option value="">— pilih sekolah —</option>
            @foreach($schools as $sc)
                <option value="{{ $sc->id }}" @selected(old('school_id', optional($profile)->school_id)==$sc->id)>{{ $sc->name }}</option>
            @endforeach
        </select>
        <label>Kelas (tingkat)</label>
        <select name="grade">
            @foreach(['X','XI','XII'] as $g)
                <option value="{{ $g }}" @selected(old('grade', optional($profile)->grade ?? 'XII')===$g)>{{ $g }}</option>
            @endforeach
        </select>
        <label>Nama Kelas</label>
        <input name="class_name" value="{{ old('class_name', optional($profile)->class_name) }}" maxlength="50" placeholder="cth: XII IPA 2 / XII IPS 1">
        <label>Nomor HP</label>
        <input name="phone" value="{{ old('phone', $user->phone) }}" maxlength="20">
        <button class="btn" style="margin-top:18px" type="submit">Simpan Profil</button>
    </form>
</div>
</div>
@endsection
