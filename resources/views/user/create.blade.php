@extends('layouts.dashboard')
@section('title', 'Tambah User')
@section('page-title', 'Tambah User')

@section('content')
<div class="card" style="max-width:600px;">
    <div class="card-header">
        <div class="card-title">👤 Tambah User Baru</div>
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

        <form method="POST" action="{{ route('user.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Nama *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email *</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Role *</label>
                <select name="role" class="form-control" required>
                    <option value="kasir" {{ old('role') === 'kasir' ? 'selected' : '' }}>Kasir</option>
                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Password *</label>
                <input type="password" name="password" class="form-control" required minlength="6">
                <small style="color:var(--muted);">Minimal 6 karakter</small>
            </div>
            <div class="form-group">
                <label class="form-label">Konfirmasi Password *</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>
            <div style="margin-top:20px;">
                <button type="submit" class="btn btn-primary">💾 Simpan</button>
                <a href="{{ route('user.index') }}" class="btn btn-ghost">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
