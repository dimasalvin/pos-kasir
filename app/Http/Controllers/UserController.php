<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Daftar semua user
     */
    public function index()
    {
        $users = User::orderByRaw("FIELD(role, 'admin', 'kasir')")
            ->orderBy('name')
            ->get();

        return view('user.index', compact('users'));
    }

    /**
     * Form tambah user
     */
    public function create()
    {
        return view('user.create');
    }

    /**
     * Simpan user baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(6)],
            'role'     => 'required|in:admin,kasir',
        ], [
            'name.required'      => 'Nama wajib diisi.',
            'email.required'     => 'Email wajib diisi.',
            'email.unique'       => 'Email sudah digunakan.',
            'password.required'  => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min'       => 'Password minimal 6 karakter.',
        ]);

        User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => $validated['role'],
        ]);

        Log::info('User baru dibuat', [
            'admin_id' => auth()->id(),
            'email'    => $validated['email'],
            'role'     => $validated['role'],
        ]);

        return redirect()->route('user.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    /**
     * Form edit user
     */
    public function edit(User $user)
    {
        return view('user.edit', compact('user'));
    }

    /**
     * Update user
     */
    public function update(Request $request, User $user)
    {
        $rules = [
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role'  => 'required|in:admin,kasir',
        ];

        // Password opsional saat edit
        if ($request->filled('password')) {
            $rules['password'] = ['confirmed', Password::min(6)];
        }

        $validated = $request->validate($rules, [
            'name.required'      => 'Nama wajib diisi.',
            'email.required'     => 'Email wajib diisi.',
            'email.unique'       => 'Email sudah digunakan.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min'       => 'Password minimal 6 karakter.',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];

        if ($request->filled('password')) {
            $user->password = Hash::make($validated['password']);
            // Force logout user yang password-nya diganti (reset login token)
            $user->login_token = null;
        }

        $user->save();

        Log::info('User diupdate', [
            'admin_id'       => auth()->id(),
            'target_user_id' => $user->id,
            'email'          => $user->email,
            'password_changed' => $request->filled('password'),
        ]);

        return redirect()->route('user.index')
            ->with('success', "User {$user->name} berhasil diperbarui.");
    }

    /**
     * Hapus user (tidak bisa hapus diri sendiri)
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        }

        // Cek apakah user punya transaksi
        if ($user->transaksis()->exists()) {
            return back()->with('error', 'User tidak bisa dihapus karena sudah memiliki data transaksi.');
        }

        Log::info('User dihapus', [
            'admin_id'       => auth()->id(),
            'target_user_id' => $user->id,
            'email'          => $user->email,
        ]);

        $user->delete();

        return redirect()->route('user.index')
            ->with('success', 'User berhasil dihapus.');
    }
}
