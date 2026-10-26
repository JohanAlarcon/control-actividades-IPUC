<?php

namespace App\Support\Sympathizers;

use App\Models\Sympathizer;

/**
 * El aviso de duplicado al registrar (CL-09, HU-13 escenario 3): si ya hay alguien con ese nombre
 * y ese telefono. Quien registra casi nunca ve la ficha de seguimiento, asi que la pregunta se
 * hace sobre toda la sede; pero responde solo si o no, como los conteos de lo que queda fuera del
 * cargo (RF-009): no trae ningun dato de la otra ficha. Advierte, no bloquea: dos personas pueden
 * llamarse igual.
 */
class DuplicateCheck
{
    public static function exists(string $nombre, ?string $telefono): bool
    {
        $telefonos = Sympathizer::withoutGlobalScope('committee')->where('name', trim($nombre))->pluck('phone');

        if ($telefonos->isEmpty()) {
            return false;
        }

        $digitos = fn (?string $texto) => preg_replace('/\D/', '', (string) $texto);

        return blank($digitos($telefono)) || $telefonos->contains(fn (?string $otro) => $digitos($otro) === $digitos($telefono));
    }
}
