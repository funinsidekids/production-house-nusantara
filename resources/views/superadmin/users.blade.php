@extends('layouts.learn')
@section('title', 'Kelola Akun')
@section('content')
<h1 class="page">Kelola Akun User &amp; Admin</h1>
<p class="sub">Hanya Superadmin yang dapat membuat, menyetujui, mengubah role, dan menghapus akun. Akun superadmin tidak ditampilkan di sini.</p>

<div class="grid c4" style="margin-bottom:18px">
    <div class="card stat"><div class="num">{{ $counts['total'] }}</div><div class="lbl">Total Akun</div></div>
    <div class="card stat"><div class="num" style="color:#ea580c">{{ $counts['pending'] }}</div><div class="lbl">Pending</div></div>
    <div class="card stat"><div class="num" style="color:#16a34a">{{ $counts['approved'] }}</div><div class="lbl">Disetujui</div></div>
    <div class="card stat"><div class="num" style="color:#dc2626">{{ $counts['rejected'] }}</div><div class="lbl">Ditolak</div></div>
</div>

<div class="card">
    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:end;margin-bottom:14px">
        <form method="GET" style="display:flex;gap:8px;flex:1;min-width:260px">
            <input name="q" value="{{ $q }}" placeholder="Cari nama / email / username…" style="max-width:300px">
            <select name="approval_status" style="max-width:170px">
                <option value="">Semua status</option>
                @foreach(['pending','approved','rejected'] as $s)
                    <option value="{{ $s }}" @selected($status===$s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <button class="btn sm dark" type="submit">Filter</button>
        </form>
        <a class="btn" href="{{ route('superadmin.users.create') }}">+ Buat Akun Baru</a>
    </div>

    <div style="overflow-x:auto">
    <table>
        <tr><th>Nama</th><th>Email / Username</th><th>Role</th><th>Status</th><th>Persetujuan</th><th>Aksi</th></tr>
        @forelse($users as $u)
            <tr>
                <td><b>{{ $u->name }}</b><div style="font-size:11.5px;color:#94a3b8">{{ $u->phone }}</div></td>
                <td style="font-size:12.5px">{{ $u->email }}<br><span style="color:#94a3b8">@{{ $u->username }}</span></td>
                <td>
                    <form method="POST" action="{{ route('superadmin.users.role', $u->id) }}">@csrf
                        <select name="role" onchange="this.form.submit()" style="width:auto;padding:4px 8px;font-size:12px">
                            <option value="user" @selected(strtolower((string)$u->primary_role)==='user')>User</option>
                            <option value="admin" @selected(strtolower((string)$u->primary_role)==='admin')>Admin</option>
                        </select>
                    </form>
                </td>
                <td><span class="pill {{ $u->status==='active'?'green':($u->status==='suspended'?'orange':'gray') }}">{{ $u->status }}</span></td>
                <td><span class="pill {{ $u->approval_status==='approved'?'green':($u->approval_status==='pending'?'gold':'red') }}">{{ $u->approval_status }}</span>
                    @if($u->rejection_reason)<div style="font-size:11px;color:#dc2626">{{ $u->rejection_reason }}</div>@endif</td>
                <td>
                    <div style="display:flex;gap:5px;flex-wrap:wrap">
                        @if($u->approval_status!=='approved')
                            <form method="POST" action="{{ route('superadmin.users.approve', $u->id) }}">@csrf<button class="btn sm ok">Setujui</button></form>
                        @else
                            <form method="POST" action="{{ route('superadmin.users.suspend', $u->id) }}">@csrf
                                <button class="btn sm {{ $u->status==='active'?'bad':'ok' }}">{{ $u->status==='active'?'Suspend':'Aktifkan' }}</button></form>
                        @endif
                        @if($u->approval_status==='pending')
                            <form method="POST" action="{{ route('superadmin.users.reject', $u->id) }}">@csrf<button class="btn sm bad">Tolak</button></form>
                        @endif
                        <details style="position:relative">
                            <summary class="btn sm ghost" style="list-style:none;cursor:pointer">⋯</summary>
                            <div style="position:absolute;right:0;top:28px;background:#fff;border:1px solid #e2e8f0;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.12);padding:10px;width:220px;z-index:5">
                                <form method="POST" action="{{ route('superadmin.users.password', $u->id) }}" style="display:flex;gap:6px">@csrf
                                    <input type="password" name="password" placeholder="Password baru (min 8)" required minlength="8" style="padding:6px 8px;font-size:12px">
                                    <button class="btn sm dark" style="white-space:nowrap">Reset</button>
                                </form>
                                <form method="POST" action="{{ route('superadmin.users.destroy', $u->id) }}" style="margin-top:8px" onsubmit="return confirm('Hapus permanen akun {{ $u->email }}?')">
                                    @csrf @method('DELETE')
                                    <button class="btn sm bad" style="width:100%">🗑 Hapus Akun</button>
                                </form>
                            </div>
                        </details>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" style="color:#64748b;text-align:center;padding:24px">Tidak ada akun.</td></tr>
        @endforelse
    </table>
    </div>
    <div style="margin-top:14px">{{ $users->links() }}</div>
</div>
@endsection
