<?php

namespace Database\Seeders;

use App\Models\Committee;
use App\Models\CommitteeUser;
use App\Models\Site;
use App\Models\SiteMotto;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Datos de demostracion. Los comites son los quince de la sede, con sus seis sub-comites, tal como
 * los trae el levantamiento (S-11). Las PERSONAS SON FICTICIAS: la constitucion prohibe datos reales
 * de personas en el repositorio, y los anexos del levantamiento no se copian (DEP-05).
 */
class DemoSeeder extends Seeder
{
    private const CLAVE = 'clave1234';

    public function run(): void
    {
        $sede = Site::create([
            'name' => 'IPUC La Esmeralda',
            'city' => 'Ibagué',
        ]);

        $comites = $this->crearEstructura($sede);
        $this->crearCuentas($sede, $comites);

        // El lema publico de la sede para este año, como lo trae su portafolio (RF-082).
        SiteMotto::create([
            'site_id' => $sede->id,
            'year' => now('America/Bogota')->year,
            'text' => 'Unánimes, teniendo un mismo sentir en la misión',
            'citation' => 'Filipenses 2:2',
            'recorded_by_user_id' => User::where('username', 'pastor')->value('id'),
        ]);
        $this->call(DemoApprovalsSeeder::class);
        $this->call(DemoCalendarSeeder::class);
        $this->call(DemoPeopleSeeder::class);

        $this->command->newLine();
        $this->command->info('Datos de demostración listos. La clave de todas las cuentas es: '.self::CLAVE);
    }

    /** @return array<string, Committee> */
    private function crearEstructura(Site $sede): array
    {
        $nodos = [];

        $estructura = [
            'Comité de Damas Dorcas' => ['Sub-comité Hijas del Rey', 'Sub-comité Mensajeros de su Gracia', 'Sub-comité Mujeres Virtuosas'],
            'Comité de Conquistadores Pentecostales' => ['Sub-comité Bethel', 'Sub-comité Emmanuel', 'Sub-comité Linaje'],
            'Comité de Misiones y Evangelismo 1' => [],
            'Comité de Misiones y Evangelismo 2' => [],
            'Comité de Misiones y Evangelismo 3' => [],
            'Comité de Obra Social' => [],
            'Comité de Alabanza' => [],
            'Comité Decom' => [],
            'Comité BIS' => [],
            'Comité de Vigilancia' => [],
            'Comité de Sonidos' => [],
            'Comité de Red de Familia' => [],
            'Comité de Crecimiento Espiritual' => [],
            'Comité de Escuela Dominical' => [],
            'Comité de Caballeros' => [],
        ];

        $posicion = 0;

        foreach ($estructura as $comite => $subComites) {
            $nodos[$comite] = $this->nodo($sede, $comite, null, $posicion++);

            foreach ($subComites as $orden => $subComite) {
                $nodos[$subComite] = $this->nodo($sede, $subComite, $nodos[$comite], $orden);
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
            'receives_sympathizers' => $nombre === 'Comité BIS',
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
            ['presidente.damas', 'Hermana Blanca Cárdenas', 'Comité de Damas Dorcas', CommitteeUser::PRESIDENT],
            ['secretaria.damas', 'Hermana Esperanza Villamil', 'Comité de Damas Dorcas', CommitteeUser::SECRETARY],
            ['lider.hijas', 'Hermana Omaira Lozano', 'Sub-comité Hijas del Rey', CommitteeUser::LEADER],
            ['presidente.conquistadores', 'Hermano Andrés Molina', 'Comité de Conquistadores Pentecostales', CommitteeUser::PRESIDENT],
            ['lider.bethel', 'Hermano Sebastián Cuervo', 'Sub-comité Bethel', CommitteeUser::LEADER],
            ['presidente.misiones1', 'Hermano Gustavo Rengifo', 'Comité de Misiones y Evangelismo 1', CommitteeUser::PRESIDENT],
            ['presidente.bis', 'Hermana Yamile Barrera', 'Comité BIS', CommitteeUser::PRESIDENT],
            ['secretario.bis', 'Hermano Germán Ávila', 'Comité BIS', CommitteeUser::SECRETARY],
            ['presidente.obrasocial', 'Hermana Carmenza Osorio', 'Comité de Obra Social', CommitteeUser::PRESIDENT],
            ['tesorero.obrasocial', 'Hermano Álvaro Pinzón', 'Comité de Obra Social', CommitteeUser::TREASURER],
            ['presidente.escuela', 'Hermana Libia Montaño', 'Comité de Escuela Dominical', CommitteeUser::PRESIDENT],
        ];

        foreach ($cargos as [$usuario, $nombre, $comite, $rol]) {
            $persona = $this->cuenta($sede, $usuario, $nombre);
            $this->cargo($sede, $persona, $comites[$comite], $rol);
        }

        // Una persona con dos cargos, para probar el selector (CL-05).
        $doble = $this->cuenta($sede, 'doble.cargo', 'Hermano Efraín Salazar');
        $this->cargo($sede, $doble, $comites['Comité de Red de Familia'], CommitteeUser::PRESIDENT);
        $this->cargo($sede, $doble, $comites['Sub-comité Emmanuel'], CommitteeUser::LEADER);

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
