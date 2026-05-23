@extends('layouts.dashboard')
@section('title', 'Manajemen User')
@section('page-title', 'Manajemen User')

@section('content')
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">👥 Daftar User</div>
            <div class="card-subtitle">{{ $users->count() }} user terdaftar</div>
        </div>
        <a href="{{ route('user.create') }}" class="btn btn-primary">+ Tambah User</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">✓ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">⚠️ {{ session('error') }}</div>
    @endif

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status Login</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $i => $user)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td style="font-weight:700;">
                        {{ $user->name }}
                        @if($user->id === auth()->id())
                            <span style="font-size:11px; color:var(--teal); font-weight:600;">(Anda)</span>
                        @endif
                    </td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <span class="badge {{ $user->role === 'admin' ? 'badge-purple' : 'badge-teal' }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td>
                        @if($user->login_token)
                            <span style="color:#28a745; font-weight:600;">● Aktif</span>
                        @else
                            <span style="color:var(--muted);">○ Offline</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('user.edit', $user) }}" class="btn btn-sm btn-ghost">✏️</a>
                        @if($user->id !== auth()->id())
                        <form method="POST" action="{{ route('user.destroy', $user) }}" style="display:inline;"
                              onsubmit="return confirm('Hapus user {{ $user->name }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">🗑️</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
