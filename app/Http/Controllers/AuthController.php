<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Single login: generate token unik, simpan di user & session
            $token = Str::random(60);
            $user = Auth::user();
            $user->update(['login_token' => $token]);
            session(['login_token' => $token]);

            Log::info('Login berhasil', [
                'user_id' => $user->id,
                'email'   => $user->email,
                'ip'      => $request->ip(),
            ]);

            return redirect()->intended(route('dashboard'));
        }

        Log::warning('Login gagal', [
            'email' => $request->email,
            'ip'    => $request->ip(),
        ]);

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            Log::info('Logout', [
                'user_id' => Auth::id(),
                'email'   => Auth::user()->email,
            ]);
            Auth::user()->update(['login_token' => null]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
