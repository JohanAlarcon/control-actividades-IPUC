<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSympathizerVisitRequest;
use App\Models\Sympathizer;
use Illuminate\Http\RedirectResponse;

/** La bitacora de visitas: cada una deja historia y mueve el compromiso, que se recalcula solo (HU-14, escenario 4). */
class SympathizerVisitController extends Controller
{
    public function store(StoreSympathizerVisitRequest $request, Sympathizer $sympathizer): RedirectResponse
    {
        $sympathizer->visits()->create([
            'site_id' => $sympathizer->site_id,
            'visited_at' => $request->validated('visited_at'),
            'type' => $request->validated('type'),
            'by_user_id' => $request->user()->id,
            'note' => $request->validated('note'),
        ]);

        return back()->with('notice', 'Listo. La visita quedó anotada y el compromiso se recalculó.');
    }
}
