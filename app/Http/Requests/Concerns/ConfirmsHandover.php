<?php

namespace App\Http\Requests\Concerns;

use App\Support\Users\AssignRole;
use Illuminate\Validation\Validator;

/**
 * Asignar un cargo que ya tiene titular releva a esa persona y le quita el
 * acceso al comite. Eso se confirma de manera explicita: no puede pasar por un
 * clic distraido.
 */
trait ConfirmsHandover
{
    protected function confirmHandover(Validator $validator, ?int $committeeId, ?int $userId): void
    {
        if (! $committeeId || ! $this->filled('role') || $validator->errors()->isNotEmpty()) {
            return;
        }

        $titular = app(AssignRole::class)->currentHolder($committeeId, $this->input('role'));

        if (! $titular) {
            return;
        }

        if ($titular->user_id === $userId) {
            $validator->errors()->add('role', 'Esa persona ya tiene ese cargo.');

            return;
        }

        if (! $this->boolean('handover')) {
            $validator->errors()->add('handover', "Ese cargo lo tiene hoy {$titular->user->name}. Si sigue, deja el cargo, y sus solicitudes pendientes pasan a quien lo reciba.");
        }
    }
}
