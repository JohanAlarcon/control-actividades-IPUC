<?php

namespace Database\Seeders;

use App\Models\Committee;
use App\Models\CommitteeUser;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Datos de demostracion. TODO ES FICTICIO: la constitucion prohibe datos reales
 * de personas en el repositorio. La estructura real la carga el pastor (DEP-05).
 */
class DemoSeeder extends Seeder
{
    private const CLAVE = 'clave1234';

    public function run(): void
    {
        $sede = Site::create([
            'name' => 'IPUC Central Ibagué',
            'city' => 'Ibagué',
        ]);

        $comites = $this->crearEstructura($sede);
        $this->crearCuentas($sede, $comites);
        $this->call(DemoApprovalsSeeder::class);

        $this->command->newLine();
        $this->command->info('Datos de demostración listos. La clave de todas las cuentas es: '.self::CLAVE);
    }

    /** @return array<string, Committee> */
    private function crearEstructura(Site $sede): array
    {
        $nodos = [];

        $estructura = [
            'Comité de Alabanza' => [
                'Sub-comité de Sonido' => ['Equipo de Montaje'],
                'Sub-comité de Coro' => [],
            ],
            'Comité de Evangelismo' => [
                'Sub-comité de Visitación' => [],
            ],
            'Comité de Damas Dorcas' => [],
            'Comité de Jóvenes' => [],
            'Comité de Red de Familia' => [],
            'Comité de Escuela Dominical' => [],
        ];

        $posicion = 0;

        foreach ($estructura as $comite => $subComites) {
            $nodos[$comite] = $this->nodo($sede, $comite, null, $posicion++);

            foreach ($subComites as $subComite => $equipos) {
                $nodos[$subComite] = $this->nodo($sede, $subComite, $nodos[$comite], 0);

                foreach ($equipos as $equipo) {
                    $nodos[$equipo] = $this->nodo($sede, $equipo, $nodos[$subComite], 0);
                }
            }
        }

        return $nodos;
    }

    private function nodo(Site $sede, string $nombre, ?Committee $padre, int $posicion): Committee
    {
        return Committee::create([
            'site_id' => $sede->id,
            'parent_id' => $padre?->id,
            'name' => $nombre,
            'slug' => Str::slug($nombre),
            'position' => $posicion,
            'active' => true,
        ]);
    }

    /** @param array<string, Committee> $comites */
    private function crearCuentas(Site $sede, array $comites): void
    {
        $pastor = $this->cuenta($sede, 'pastor', 'Pastor Hernán Ospina', esPastor: true);

        $cargos = [
            ['presidente.alabanza', 'Hermano Rigoberto Páez', 'Comité de Alabanza', CommitteeUser::PRESIDENT],
            ['secretaria.alabanza', 'Hermana Luz Mary Quintero', 'Comité de Alabanza', CommitteeUser::SECRETARY],
            ['tesorero.alabanza', 'Hermano Jairo Betancur', 'Comité de Alabanza', CommitteeUser::TREASURER],
            ['lider.sonido', 'Hermano Andrés Molina', 'Sub-comité de Sonido', CommitteeUser::LEADER],
            ['lider.montaje', 'Hermano Wilson Garzón', 'Equipo de Montaje', CommitteeUser::LEADER],
            ['lider.coro', 'Hermana Omaira Lozano', 'Sub-comité de Coro', CommitteeUser::LEADER],
            ['presidente.evangelismo', 'Hermano Gustavo Rengifo', 'Comité de Evangelismo', CommitteeUser::PRESIDENT],
            ['presidente.damas', 'Hermana Blanca Cárdenas', 'Comité de Damas Dorcas', CommitteeUser::PRESIDENT],
        ];

        foreach ($cargos as [$usuario, $nombre, $comite, $rol]) {
            $persona = $this->cuenta($sede, $usuario, $nombre);
            $this->cargo($sede, $persona, $comites[$comite], $rol);
        }

        // Una persona con dos cargos, para probar el selector (CL-05).
        $doble = $this->cuenta($sede, 'doble.cargo', 'Hermano Efraín Salazar');
        $this->cargo($sede, $doble, $comites['Comité de Jóvenes'], CommitteeUser::PRESIDENT);
        $this->cargo($sede, $doble, $comites['Sub-comité de Visitación'], CommitteeUser::LEADER);

        // Una cuenta recien creada, para ver el cambio obligatorio de clave.
        $this->cuenta($sede, 'nuevo.lider', 'Hermano Camilo Ruiz', debeCambiar: true);

        $pastor->save();
    }

    private function cuenta(Site $sede, string $usuario, string $nombre, bool $esPastor = false, bool $debeCambiar = false): User
    {
        return User::create([
            'site_id' => $sede->id,
            'name' => $nombre,
            'username' => $usuario,
            'email' => null,
            'password' => Hash::make(self::CLAVE),
            'must_change_password' => $debeCambiar,
            'is_pastor' => $esPastor,
            'active' => true,
        ]);
    }

    private function cargo(Site $sede, User $persona, Committee $comite, string $rol): void
    {
        CommitteeUser::create([
            'site_id' => $sede->id,
            'user_id' => $persona->id,
            'committee_id' => $comite->id,
            'role' => $rol,
            'started_at' => now()->subMonths(8),
        ]);
    }
}
