<?php

namespace App\Http\Controllers;

use App\Http\Middleware\ResolveScope;
use App\Models\CommitteeUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Cambiar el cargo con el que la persona esta actuando.
 *
 * El ambito de una peticion es el de UN cargo, nunca la union de dos: para ver
 * el otro, se cambia de cargo.
 */
class RoleSwitchController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'roleId' => ['required', 'integer'],
        ]);

        $cargo = CommitteeUser::query()
            ->where('user_id', $request->user()->id)
            ->whereNull('ended_at')
            ->find($datos['roleId']);

        if (! $cargo) {
            return back()->with('notice', 'Ese cargo no está disponible para usted.');
        }

        $request->session()->put(ResolveScope::SESSION_KEY, $cargo->id);

        return back()->with('notice', 'Ahora está actuando como '.$cargo->committee?->name.'.');
    }
}
