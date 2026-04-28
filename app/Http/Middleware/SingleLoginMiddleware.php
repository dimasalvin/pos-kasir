<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SingleLoginMiddleware
{
    /**
     * Cek apakah session login_token masih cocok dengan yang di database.
     * Jika tidak cocok, berarti user sudah login di perangkat lain → paksa logout.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $sessionToken = session('login_token');

            // Jika token di session tidak cocok dengan token di database,
            // berarti ada login baru di perangkat lain
            if ($user->login_token && $sessionToken !== $user->login_token) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => 'Akun Anda telah login di perangkat lain. Sesi ini telah berakhir.',
                ]);
            }
        }

        return $next($request);
    }
}
