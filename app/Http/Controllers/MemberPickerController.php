<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Support\Scope\CurrentScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * El buscador del padrón (constitución 3.0.0, principio I, excepción 4; RF-085): para que un
 * comité escoja a quién agregar a su lista. De cada persona que coincide trae el nombre y la edad,
 * y nada más: ni sus comités, ni su teléfono, ni su barrio.
 */
class MemberPickerController extends Controller
{
    private const MAXIMO = 20;

    public function __invoke(Request $request, CurrentScope $scope): JsonResponse
    {
        abort_unless($scope->isPastor() || $scope->activeRole() !== null, 403);

        $nombre = trim((string) $request->query('q'));

        if (mb_strlen($nombre) < 2) {
            return response()->json(['results' => []]);
        }

        return response()->json([
            'results' => Member::forPicker($nombre)->limit(self::MAXIMO)->get()->map(fn (Member $persona) => [
                'id' => $persona->id,
                'name' => $persona->name,
                'age' => $persona->age(),
            ]),
        ]);
    }
}
