@extends('layouts.learn')
@section('title', 'Daftar Akun Baru')
@section('content')
<div style="max-width:460px;margin:30px auto">
    <div class="card">
        <h1 class="page">Daftar Akun Baru</h1>
        <p class="sub">Setelah mendaftar, akun Anda berstatus <b>menunggu persetujuan Superadmin</b> sebelum dapat login.</p>

        @if(isset($errors) && $errors->any())<div class="flash err">⚠️ {{ $errors->first() }}</div>@endif

        <form method="POST" action="{{ route('register.store') }}">
            @csrf
            <label>Nama Lengkap</label>
            <input name="name" value="{{ old('name') }}" required maxlength="120">

            <label>Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required maxlength="190">

            <label>Username</label>
            <input name="username" value="{{ old('username') }}" required minlength="4" maxlength="60" placeholder="minimal 4 karakter, tanpa spasi">

            <label>Nomor HP (opsional)</label>
            <input name="phone" value="{{ old('phone') }}" maxlength="20" placeholder="08xxxxxxxxxx">

            <label>Password</label>
            <input type="password" name="password" required minlength="8">

            <label>Ulangi Password</label>
            <input type="password" name="password_confirmation" required minlength="8">

            <button class="btn" style="width:100%;margin-top:18px" type="submit">DAFTAR — MENUNGGU PERSETUJUAN</button>
        </form>

        <p style="margin-top:14px;font-size:13px;text-align:center;color:#64748b">
            Sudah punya akun? <a href="{{ route('home') }}" style="font-weight:700;color:#0b1f3a">Masuk di sini</a>
        </p>
    </div>
</div>
@endsection
