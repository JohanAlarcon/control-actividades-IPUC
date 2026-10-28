<?php

namespace Database\Seeders;

use App\Models\Committee;
use App\Models\CommitteeMember;
use App\Models\Member;
use App\Models\Sympathizer;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Miembros y simpatizantes de ejemplo. TODO ES FICTICIO: la constitucion prohibe datos reales de
 * personas en el repositorio. El padron real lo carga la congregacion (DEP-05). Necesita las
 * cuentas y los comites de DemoSeeder.
 */
class DemoPeopleSeeder extends Seeder
{
    /** Nombre, genero, fecha de nacimiento o edad anotada, estado civil, año de bautismo y comite. */
    private const MIEMBROS = [
        ['Hermano Julián Restrepo', 'm', '1979-04-12', 'married', 1998, 'Comité de Alabanza'],
        ['Hermana Marleny Ortiz', 'f', '1984-09-30', 'married', 2003, 'Sub-comité de Coro'],
        ['Hermano Fabio Cárdenas', 'm', 52, 'married', 1995, 'Sub-comité de Sonido'],
        ['Hermana Gloria Patiño', 'f', '1961-02-03', 'widowed', 1980, 'Comité de Damas Dorcas'],
        ['Hermano Óscar Niño', 'm', '2009-07-21', 'single', 2022, 'Comité de Jóvenes'],
        ['Hermana Luz Dary Hernández', 'f', '1990-11-15', 'married', 2010, 'Comité de Damas Dorcas'],
        ['Hermano Wilmer Quintero', 'm', '1975-06-08', 'married', 1999, 'Comité de Evangelismo'],
        ['Hermana Rubiela Sánchez', 'f', 67, 'widowed', 1977, 'Comité de Damas Dorcas'],
        ['Hermano Camilo Bermúdez', 'm', '2004-03-02', 'single', 2019, 'Comité de Jóvenes'],
        ['Hermana Diana Marcela Rojas', 'f', '1998-12-24', 'single', 2016, 'Sub-comité de Coro'],
        ['Hermano Héctor Fabio Lozada', 'm', '1969-08-19', 'married', 1990, 'Comité de Red de Familia'],
        ['Hermana Yolanda Pérez', 'f', '1972-01-27', 'separated', 1996, 'Comité de Escuela Dominical'],
        ['Hermano Brayan Stiven Ruiz', 'm', '2011-05-05', 'single', null, 'Comité de Escuela Dominical'],
        ['Hermana Nubia Aguirre', 'f', null, 'married', 1985, 'Sub-comité de Visitación'],
        ['Hermano Luis Alfonso Mejía', 'm', '1981-10-10', 'common_law', null, 'Sub-comité de Sonido'],
        ['Hermana Paola Andrea Díaz', 'f', '1995-04-18', 'married', 2014, null],
        ['Hermano Gustavo Arango', 'm', 45, 'married', 2001, null],
    ];

    /** Nombre, genero, dias desde el primer contacto, etapa alcanzada y dias atras de cada contacto. */
    private const SIMPATIZANTES = [
        ['Andrea Milena Castro', 'f', 40, 3, [3, 9, 20, 33]],
        ['Jhon Fredy Ocampo', 'm', 25, 2, [5, 12]],
        ['Sandra Patricia Mora', 'f', 120, 2, [70, 95]],
        ['Edwin Alexis Toro', 'm', 200, 5, [2, 10, 18, 30, 45]],
        ['Liliana Vargas', 'f', 8, 1, []],
        ['Robinson Gaviria', 'm', 150, 1, [130]],
    ];

    public function run(): void
    {
        $pastor = User::where('username', 'pastor')->firstOrFail();
        Committee::where('site_id', $pastor->site_id)->where('name', 'Comité de Evangelismo')->update(['follows_sympathizers' => true]);

        foreach (self::MIEMBROS as $i => [$nombre, $genero, $edad, $civil, $bautismo, $comite]) {
            $miembro = Member::create([
                'site_id' => $pastor->site_id,
                'name' => $nombre,
                'birth_date' => is_string($edad) ? $edad : null,
                'age_reported' => is_int($edad) ? $edad : null,
                'age_reported_at' => is_int($edad) ? now()->subMonths(8)->toDateString() : null,
                'gender' => $genero,
                'marital_status' => $civil,
                'adult_confirmed' => $edad === null,
                'adult_confirmed_at' => $edad === null ? now()->subMonths(2)->toDateString() : null,
                'baptized' => $bautismo !== null,
                'baptized_at' => $bautismo ? "{$bautismo}-06-15" : null,
                'phone' => '310 555 '.str_pad((string) (1200 + $i), 4, '0', STR_PAD_LEFT),
                'neighborhood' => ['Picaleña', 'El Salado', 'Jordán', 'La Pola', 'Ambalá'][$i % 5],
                'profile_pending' => $edad === null,
                'data_consent' => $i % 4 !== 3,
                'data_consent_at' => $i % 4 !== 3 ? now()->subMonths(3)->toDateString() : null,
                'data_consent_means' => $i % 4 !== 3 ? ['signature', 'verbal', 'message'][$i % 3] : null,
            ]);

            if ($comite) {
                CommitteeMember::create([
                    'site_id' => $pastor->site_id,
                    'member_id' => $miembro->id,
                    'committee_id' => Committee::where('site_id', $pastor->site_id)->where('name', $comite)->value('id'),
                    'role' => 'member',
                    'since' => now()->subYears(2)->toDateString(),
                ]);
            }
        }

        $evangelismo = Committee::where('site_id', $pastor->site_id)->where('follows_sympathizers', true)->firstOrFail();
        $invita = User::where('username', 'lider.sonido')->value('id') ?? $pastor->id;
        $acompana = User::where('username', 'presidente.evangelismo')->value('id') ?? $pastor->id;

        foreach (self::SIMPATIZANTES as $i => [$nombre, $genero, $hace, $etapa, $contactos]) {
            $persona = Sympathizer::create([
                'site_id' => $evangelismo->site_id,
                'committee_id' => $evangelismo->id,
                'name' => $nombre,
                'age_reported' => 25 + $i * 6,
                'age_reported_at' => now()->subDays($hace)->toDateString(),
                'gender' => $genero,
                'phone' => '320 555 '.str_pad((string) (3400 + $i), 4, '0', STR_PAD_LEFT),
                'neighborhood' => ['Picaleña', 'El Salado', 'Jordán'][$i % 3],
                'arrival_source' => Sympathizer::ARRIVAL_SOURCES[$i % 6],
                'invited_by_user_id' => $invita,
                'first_contact_at' => now()->subDays($hace)->toDateString(),
                'stage' => $etapa,
                'data_consent' => true,
                'data_consent_at' => now()->subDays($hace)->toDateString(),
                'data_consent_means' => 'verbal',
            ]);

            foreach (range(1, $etapa) as $n) {
                $persona->stages()->create([
                    'site_id' => $persona->site_id,
                    'stage' => $n,
                    'reached_at' => now()->subDays((int) round($hace * (1 - ($n - 1) / max($etapa, 1)))),
                    'recorded_by_user_id' => $acompana,
                ]);
            }

            foreach ($contactos as $j => $dias) {
                $persona->visits()->create([
                    'site_id' => $persona->site_id,
                    'visited_at' => now()->subDays($dias)->toDateString(),
                    'type' => ['service', 'visit', 'call', 'study'][$j % 4],
                    'by_user_id' => $acompana,
                    'note' => $j === 0 ? 'Vino con su familia y se quedó al final del culto.' : null,
                ]);
            }
        }
    }
}
