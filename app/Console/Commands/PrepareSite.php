<?php

namespace App\Console\Commands;

use App\Models\Committee;
use App\Models\CommitteeUser;
use App\Models\Site;
use App\Models\User;
use App\Support\Committees\SiteStructure;
use App\Support\Users\AssignRole;
use App\Support\Users\RoleUsername;
use App\Support\Users\TemporaryPassword;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Prepara la sede en el servidor (RF-123). Si la base está vacía, crea la sede con la estructura
 * del levantamiento. Después le da una cuenta a cada cargo sin titular, y al pastor si no la tiene:
 * el usuario dice el cargo y el comité, y la clave inicial son seis números que la persona cambia
 * al entrar. El cuadro para entregarlas sale una sola vez, en la pantalla y en un archivo privado
 * que se borra después de repartirlas. Correrlo otra vez no toca las cuentas que ya existen.
 */
class PrepareSite extends Command
{
    protected $signature = 'site:prepare';

    protected $description = 'Prepara la sede: su estructura de comités y una cuenta por cargo, con clave inicial de solo números';

    private const ROLES = [
        1 => [CommitteeUser::PRESIDENT, CommitteeUser::SECRETARY, CommitteeUser::TREASURER],
        2 => [CommitteeUser::LEADER],
    ];

    private const LABELS = [
        CommitteeUser::PRESIDENT => 'Presidente',
        CommitteeUser::SECRETARY => 'Secretario',
        CommitteeUser::TREASURER => 'Tesorero',
        CommitteeUser::LEADER => 'Líder',
    ];

    public function handle(AssignRole $cargos): int
    {
        $sede = Site::query()->oldest('id')->first() ?? Site::create(['name' => 'IPUC La Esmeralda', 'city' => 'Ibagué']);

        if (! Committee::where('site_id', $sede->id)->exists()) {
            SiteStructure::create($sede);
            $this->info("{$sede->name} no tenía comités: quedó con los del levantamiento.");
        }

        $filas = [];

        if (! User::where('site_id', $sede->id)->where('is_pastor', true)->exists()) {
            $filas[] = $this->cuenta($sede, 'pastor', 'Pastor', 'Pastor', 'Toda la sede', esPastor: true)[1];
        }

        $comites = Committee::where('site_id', $sede->id)->where('active', true)->orderBy('position')->orderBy('name')->get();

        foreach ($comites->whereNull('parent_id') as $comite) {
            foreach ([$comite, ...$comites->where('parent_id', $comite->id)] as $nodo) {
                foreach (self::ROLES[$nodo->depth] ?? [] as $rol) {
                    if ($cargos->currentHolder($nodo->id, $rol)) {
                        continue;
                    }

                    [$persona, $fila] = $this->cuenta($sede, $this->libre(RoleUsername::for($rol, $nodo->name)), self::LABELS[$rol].' · '.$nodo->name, self::LABELS[$rol], $nodo->name);
                    $cargos->handle($persona, $nodo, $rol);
                    $filas[] = $fila;
                }
            }
        }

        if ($filas === []) {
            $this->info('Todos los cargos ya tienen cuenta. No se creó ninguna.');

            return self::SUCCESS;
        }

        $archivo = 'cuentas-'.now()->format('Y-m-d-His').'.csv';
        Storage::disk('local')->put($archivo, $this->cuadro($filas));

        $this->table(['Cargo', 'Comité', 'Usuario', 'Clave inicial'], $filas);
        $this->info(count($filas).' cuentas nuevas. Cada persona cambia su clave al entrar por primera vez.');
        $this->warn('El cuadro quedó en '.Storage::disk('local')->path($archivo).'. Entregue a cada persona solo su fila y borre el archivo después: las claves no se guardan en ninguna otra parte.');

        return self::SUCCESS;
    }

    /** @return array{0: User, 1: array<int, string>} la cuenta y su fila del cuadro */
    private function cuenta(Site $sede, string $usuario, string $nombre, string $cargo, string $comite, bool $esPastor = false): array
    {
        $clave = TemporaryPassword::numerica();

        $persona = User::create([
            'site_id' => $sede->id,
            'name' => $nombre,
            'username' => $usuario,
            'password' => $clave,
            'must_change_password' => true,
            'is_pastor' => $esPastor,
            'active' => true,
        ]);

        return [$persona, [$cargo, $comite, $usuario, $clave]];
    }

    /** Si el usuario ya lo tiene otra cuenta, se le agrega un número: «presidente.alabanza2». */
    private function libre(string $usuario): string
    {
        $libre = $usuario;
        $numero = 2;

        while (User::where('username', $libre)->exists()) {
            $libre = $usuario.$numero++;
        }

        return $libre;
    }

    /** El cuadro en CSV con punto y coma y marca UTF-8, para que Excel en español lo abra con sus tildes. */
    private function cuadro(array $filas): string
    {
        $lineas = array_map(fn (array $fila) => implode(';', $fila), [['Cargo', 'Comité', 'Usuario', 'Clave inicial'], ...$filas]);

        return "\u{FEFF}".implode("\r\n", $lineas)."\r\n";
    }
}
