@extends('layouts.learn')
@section('title', 'Buat Akun Baru')
@section('content')
<div style="max-width:520px;margin:10px auto">
    <div class="card">
        <h1 class="page">+ Buat Akun Baru</h1>
        <p class="sub">Akun yang dibuat Superadmin langsung berstatus <b>aktif &amp; disetujui</b> (tanpa perlu approval).</p>

        @if($errors->any())<div class="flash err">⚠️ {{ $errors->first() }}</div>@endif

        <form method="POST" action="{{ route('superadmin.users.store') }}">
            @csrf
            <label>Nama Lengkap</label>
            <input name="name" value="{{ old('name') }}" required maxlength="120">

            <label>Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required maxlength="190">

            <label>Username</label>
            <input name="username" value="{{ old('username') }}" required minlength="4" maxlength="60">

            <label>Nomor HP (opsional)</label>
            <input name="phone" value="{{ old('phone') }}" maxlength="20">

            <label>Role</label>
            <select name="role" required>
                <option value="user" @selected(old('role')==='user')>User (Siswa)</option>
                <option value="admin" @selected(old('role')==='admin')>Admin (Kelola Konten)</option>
            </select>

            <label>Password (min. 8 karakter)</label>
            <input type="password" name="password" required minlength="8">

            <button class="btn" style="width:100%;margin-top:20px" type="submit">Buat Akun</button>
        </form>
    </div>
</div>
@endsection
