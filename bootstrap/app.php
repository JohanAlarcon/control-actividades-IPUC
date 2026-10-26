<?php

use App\Http\Middleware\EnsureActiveUser;
use App\Http\Middleware\EnsurePasswordChanged;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\ResolveScope;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            EnsureActiveUser::class,
            ResolveScope::class,
            EnsurePasswordChanged::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        // El filtro del cargo se activa ANTES de buscar el registro de la ruta. Si no,
        // abrir /committees/5 buscaria el comite con el filtro apagado y traeria uno
        // ajeno (principio I). DirectAccessTest vigila este orden.
        $middleware->prependToPriorityList(SubstituteBindings::class, ResolveScope::class);
        $middleware->prependToPriorityList(ResolveScope::class, EnsureActiveUser::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Lo ajeno, lo que no se permite y lo que no existe muestran la misma pagina,
        // sin un solo dato de lo que se intento abrir (RF-008).
        $exceptions->respond(function (Response $response, Throwable $e, Request $request) {
            if (! in_array($response->getStatusCode(), [403, 404], true)) {
                return $response;
            }

            return Inertia::render('Errors/OutOfScope')
                ->toResponse($request)
                ->setStatusCode($response->getStatusCode());
        });
    })->create();
