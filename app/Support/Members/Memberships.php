<?php

namespace App\Support\Members;

use App\Models\CommitteeMember;
use App\Models\Member;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Entrar y salir de la lista de un comite (D-32, RF-085), en un solo lugar: la pertenencia nueva
 * empieza hoy, y la que se cierra queda con su fecha, nunca se borra. Una persona no esta dos veces
 * en la lista del mismo comite.
 */
class Memberships
{
    private const ZONE = 'America/Bogota';

    public static function join(Member $miembro, int $comite, string $funcion = 'member', ?string $cargo = null): CommitteeMember
    {
        $yaEsta = CommitteeMember::current()
            ->where('member_id', $miembro->id)
            ->where('committee_id', $comite)
            ->exists();

        if ($yaEsta) {
            throw ValidationException::withMessages(['member_id' => "{$miembro->name} ya está en la lista de este comité."]);
        }

        return CommitteeMember::create([
            'site_id' => $miembro->site_id,
            'member_id' => $miembro->id,
            'committee_id' => $comite,
            'role' => $funcion,
            'board_position' => $cargo,
            'since' => now(self::ZONE)->toDateString(),
        ]);
    }

    public static function leave(CommitteeMember $pertenencia): void
    {
        $pertenencia->update(['ended_at' => now(self::ZONE)->toDateString()]);
    }

    /**
     * Lo que el pastor marca en la ficha: entra a los comites nuevos, sale de los que desmarco, y
     * quien seguia en un comite conserva su funcion y su fecha.
     *
     * @param  array<int>  $comites
     */
    public static function sync(Member $miembro, array $comites): void
    {
        /** @var Collection<int, CommitteeMember> $vigentes */
        $vigentes = CommitteeMember::current()->where('member_id', $miembro->id)->get();

        $vigentes->reject(fn (CommitteeMember $pertenencia) => in_array($pertenencia->committee_id, $comites, true))->each(self::leave(...));

        collect($comites)
            ->diff($vigentes->pluck('committee_id'))
            ->each(fn (int $comite) => self::join($miembro, $comite));
    }
}
