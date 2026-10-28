<?php

namespace App\Http\Controllers;

use App\Models\Committee;
use App\Models\Sympathizer;
use App\Support\Sympathizers\Assignments;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * BIS reasigna a un simpatizante a otro comite (RF-097, HU-14 escenario 5): deja de verlo el comite
 * anterior, lo ve el nuevo con toda su historia, y el cambio queda escrito.
 */
class SympathizerAssignmentController extends Controller
{
    public function store(Request $request, Sympathizer $sympathizer): RedirectResponse
    {
        Gate::authorize('assign', $sympathizer);

        $datos = $request->validate(
            ['committee_id' => ['required', 'integer', Rule::in(Committee::forAssignment()->pluck('id'))]],
            ['committee_id.required' => 'Escoja el comité que la va a acompañar.', 'committee_id.integer' => 'Escoja uno de los comités de la lista.', 'committee_id.in' => 'Escoja uno de los comités de la lista.'],
        );

        if ((int) $datos['committee_id'] === $sympathizer->committee_id) {
            throw ValidationException::withMessages(['committee_id' => 'Ya está asignada a ese comité.']);
        }

        Assignments::assign($sympathizer, (int) $datos['committee_id'], $request->user());
        $nombre = Committee::forAssignment()->firstWhere('id', (int) $datos['committee_id'])['name'];

        return back()->with('notice', "Listo. {$sympathizer->name} quedó asignada a {$nombre}. El comité anterior ya no la ve.");
    }
}
