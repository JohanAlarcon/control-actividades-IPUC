<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Quien deja un cargo pierde el acceso el mismo dia (RF-006, RN-09). La cuenta
 * nunca se borra, asi que su nombre sigue en la historia; lo que se corta es la
 * sesion, en la peticion siguiente al retiro.
 */
class EnsureActiveUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if ($usuario && ! $usuario->active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'username' => 'Su acceso fue retirado. Si cree que es un error, hable con el pastor.',
            ]);
        }

        return $next($request);
    }
}
