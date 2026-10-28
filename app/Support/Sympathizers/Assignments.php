<?php

namespace App\Support\Sympathizers;

use App\Models\Committee;
use App\Models\Sympathizer;
use App\Models\SympathizerAssignment;
use App\Models\User;

/**
 * A qué comité queda asignado un simpatizante (D-34, RF-097, CL-29), en un solo lugar: cambiarlo
 * siempre deja escrita la asignación. Un simpatizante tiene un solo comité a la vez.
 */
class Assignments
{
    public static function assign(Sympathizer $persona, int $comite, ?User $quien): void
    {
        $persona->update(['committee_id' => $comite]);

        SympathizerAssignment::create([
            'site_id' => $persona->site_id,
            'sympathizer_id' => $persona->id,
            'committee_id' => $comite,
            'assigned_by_user_id' => $quien?->id,
            'assigned_at' => now(),
        ]);
    }

    /**
     * Cuando un comité se inactiva, sus simpatizantes vuelven a BIS con toda su historia (CL-29),
     * para que nadie quede sin quién lo acompañe. Si la sede no tiene BIS, se quedan donde están.
     * Inactivar un comité es cosa del pastor, que ve a todos los simpatizantes de la sede.
     */
    public static function returnToReception(Committee $cerrado): void
    {
        $bis = Committee::receivingSympathizers($cerrado->site_id);

        if ($bis === null || $bis->id === $cerrado->id) {
            return;
        }

        Sympathizer::where('committee_id', $cerrado->id)
            ->get()
            ->each(fn (Sympathizer $persona) => self::assign($persona, $bis->id, null));
    }
}
