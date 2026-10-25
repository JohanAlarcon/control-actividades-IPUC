<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Con la clave temporal todavia sin cambiar, NINGUNA otra ruta responde con
 * informacion (RF-002, HU-01 escenario 1).
 */
class EnsurePasswordChanged
{
    /** Rutas que siguen disponibles mientras la clave no se cambia. */
    private const PERMITIDAS = ['password.change', 'password.change.store', 'logout'];

    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if (! $usuario || ! $usuario->must_change_password) {
            return $next($request);
        }

        if (in_array($request->route()?->getName(), self::PERMITIDAS, true)) {
            return $next($request);
        }

        return redirect()->route('password.change');
    }
}
