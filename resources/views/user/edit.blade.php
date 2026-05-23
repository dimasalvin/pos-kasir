@extends('layouts.dashboard')
@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('content')
<div class="card" style="max-width:600px;">
    <div class="card-header">
        <div class="card-title">✏️ Edit User: {{ $user->name }}</div>
        <a href="{{ route('user.index') }}" class="btn btn-ghost btn-sm">← Kembali</a>
    </div>
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>⚠️ {{ $error }}</div>
                @endforeach
            </div>
        @endif

        @if($user->id === auth()->id())
            <div class="alert alert-info" style="background:#EEF5FD; border:1px solid #5BA4E5; color:#1A2B3C;">
                ℹ️ Anda sedang mengedit akun sendiri. Jika mengubah password, Anda akan tetap login.
            </div>
        @endif

        <form method="POST" action="{{ route('user.update', $user) }}">
            @csrf @method('PUT')
            <div class="form-group">
                <label class="form-label">Nama *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email *</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Role *</label>
                <select name="role" class="form-control" required>
                    <option value="kasir" {{ old('role', $user->role) === 'kasir' ? 'selected' : '' }}>Kasir</option>
                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
                @if($user->id === auth()->id())
                    <small style="color:var(--coral);">⚠️ Hati-hati mengubah role akun sendiri</small>
                @endif
            </div>

            <hr style="margin:20px 0; border:none; border-top:1px solid var(--border);">
            <p style="font-size:13px; color:var(--muted); margin-bottom:12px;">
                Kosongkan password jika tidak ingin mengubah
            </p>

            <div class="form-group">
                <label class="form-label">Password Baru</label>
                <input type="password" name="password" class="form-control" minlength="6" placeholder="Kosongkan jika tidak diubah">
                <small style="color:var(--muted);">Minimal 6 karakter</small>
            </div>
            <div class="form-group">
                <label class="form-label">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi password baru">
            </div>
            <div style="margin-top:20px;">
                <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
                <a href="{{ route('user.index') }}" class="btn btn-ghost">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
