<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\MonthNote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

/**
 * Las notas del mes del cronograma (RF-118), que escribe el pastor: una campaña de todo el mes o un
 * evento con la fecha pendiente. Si deja de valer, se quita.
 */
class MonthNoteController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('is-pastor');
        $hoy = now('America/Bogota')->year;

        $datos = $request->validate([
            'year' => ['required', 'integer', Rule::in([$hoy, $hoy + 1])],
            'month' => ['required', 'integer', 'between:1,12'],
            'text' => ['required', 'string', 'max:160'],
            'highlight' => ['nullable', Rule::in(Activity::HIGHLIGHTS)],
        ], [
            'year.in' => "Las notas son para {$hoy} o para ".($hoy + 1).'.',
            'month.between' => 'Escoja uno de los doce meses.',
            'text.required' => 'Escriba la nota del mes.',
            'text.max' => 'La nota es muy larga. Resúmala en menos de 160 letras.',
            'highlight.in' => 'Escoja una de las formas de destacarla que se ofrecen.',
        ]);

        MonthNote::create($datos + ['site_id' => $request->user()->site_id, 'recorded_by_user_id' => $request->user()->id]);

        return back()->with('notice', 'Listo. La nota quedó en el mes, en la pantalla y en la hoja impresa.');
    }

    public function destroy(MonthNote $note): RedirectResponse
    {
        Gate::authorize('is-pastor');
        $note->delete();

        return back()->with('notice', 'Listo. La nota se quitó del mes.');
    }
}
