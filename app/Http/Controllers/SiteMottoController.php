<?php

namespace App\Http\Controllers;

use App\Models\SiteMotto;
use App\Support\Site\SiteIdentity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * El lema de cada año (RF-082, HU-25), que registra el pastor junto con el cronograma general: el
 * del año en curso o el del que viene. Los de años anteriores quedan en la historia.
 */
class SiteMottoController extends Controller
{
    private const ZONE = 'America/Bogota';

    public function index(): Response
    {
        Gate::authorize('is-pastor');
        $hoy = now(self::ZONE)->year;

        return Inertia::render('Site/Motto', [
            'years' => [$hoy, $hoy + 1],
            'mottos' => SiteMotto::query()
                ->leftJoin('users', 'users.id', '=', 'site_mottos.recorded_by_user_id')
                ->orderByDesc('site_mottos.year')
                ->get(['site_mottos.year', 'site_mottos.text', 'site_mottos.citation', 'users.name as recorded_by_name'])
                ->map(fn (SiteMotto $lema) => [
                    'year' => $lema->year,
                    'text' => $lema->text,
                    'citation' => $lema->citation,
                    'recordedBy' => $lema->recorded_by_name,
                    'current' => $lema->year === $hoy,
                ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('is-pastor');
        $hoy = now(self::ZONE)->year;

        $datos = $request->validate([
            'year' => ['required', 'integer', Rule::in([$hoy, $hoy + 1])],
            'text' => ['required', 'string', 'max:160'],
            'citation' => ['nullable', 'string', 'max:60'],
        ], [
            'year.required' => 'Escoja el año del lema.',
            'year.in' => "El lema se registra para {$hoy} o para ".($hoy + 1).'.',
            'text.required' => 'Escriba el lema.',
            'text.max' => 'El lema es muy largo. Resúmalo en menos de 160 letras.',
            'citation.max' => 'La cita es muy larga. Por ejemplo: Filipenses 2:2.',
        ]);

        SiteMotto::updateOrCreate(
            ['site_id' => $request->user()->site_id, 'year' => $datos['year']],
            ['text' => $datos['text'], 'citation' => $datos['citation'] ?? null, 'recorded_by_user_id' => $request->user()->id],
        );
        SiteIdentity::forget($request->user()->site_id, (int) $datos['year']);

        return back()->with('notice', "Listo. El lema de {$datos['year']} quedó guardado.".((int) $datos['year'] === $hoy ? ' Ya se ve en la entrada y en todas las pantallas.' : ' Se verá desde el primero de enero.'));
    }
}
