<?php

namespace App\Http\Middleware;

use App\Support\Scope\CurrentScope;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resuelve el ambito una sola vez por peticion, antes de que cualquier
 * consulta toque la base. El cargo activo vive en la sesion.
 */
class ResolveScope
{
    public const SESSION_KEY = 'active_role_id';

    public function __construct(private readonly CurrentScope $scope) {}

    public function handle(Request $request, Closure $next): Response
    {
        $this->scope->resolve(
            $request->user(),
            $request->session()->get(self::SESSION_KEY)
        );

        return $next($request);
    }
}
