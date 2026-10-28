<?php

namespace App\Http\Controllers;

use App\Models\Sympathizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Mientras se escribe el nombre, los ya registrados que coinciden (RF-096, HU-13 escenario 2), para
 * no registrar dos veces a la misma persona. Salen solo del ámbito: a BIS, todos; a un comité, los
 * suyos. Lo de afuera lo cubre el aviso de duplicado, que solo dice sí o no (CL-09).
 */
class SympathizerSuggestController extends Controller
{
    private const MAXIMO = 8;

    public function __invoke(Request $request): JsonResponse
    {
        Gate::authorize('create', Sympathizer::class);

        $nombre = addcslashes(trim((string) $request->query('q')), '%_\\');

        if (mb_strlen($nombre) < 2) {
            return response()->json(['results' => []]);
        }

        return response()->json([
            'results' => Sympathizer::query()
                ->where(fn ($palabra) => $palabra->where('name', 'like', $nombre.'%')->orWhere('name', 'like', '% '.$nombre.'%'))
                ->orderBy('name')
                ->limit(self::MAXIMO)
                ->get(['id', 'name', 'phone'])
                ->map(fn (Sympathizer $persona) => ['id' => $persona->id, 'name' => $persona->name, 'phone' => $persona->phone]),
        ]);
    }
}
