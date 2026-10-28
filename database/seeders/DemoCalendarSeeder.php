<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\ActivityType;
use App\Models\AnnualPlan;
use App\Models\Committee;
use App\Models\CommitteeUser;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Un año de actividades para ver el cronograma lleno (T087). TODO ES FICTICIO. El plan de este año
 * ya aprobado por el pastor, con lo pasado cumplido o en camino y lo que viene por hacer; y el plan
 * del año siguiente cargado, esperando su aprobacion. Necesita las cuentas de DemoSeeder.
 */
class DemoCalendarSeeder extends Seeder
{
    /** Lo que cada comite hace en el año: titulo, clase y mes. */
    private const PLAN = [
        'Comité de Alabanza' => [['Culto de alabanza', 'Culto', 2], ['Ensayo general', 'Ensayo', 5], ['Noche de alabanza', 'Evento', 9], ['Culto de Navidad', 'Culto', 12]],
        'Sub-comité de Sonido' => [['Revisión de los equipos de sonido', 'Reunión', 3], ['Capacitación de sonido', 'Reunión', 8]],
        'Sub-comité de Coro' => [['Ensayo del coro para la Santa Cena', 'Ensayo', 4], ['Cantata de Navidad', 'Evento', 12]],
        'Comité de Evangelismo' => [['Campaña evangelística', 'Campaña', 3], ['Salida a evangelizar', 'Salida', 7], ['Campaña de fin de año', 'Campaña', 11]],
        'Sub-comité de Visitación' => [['Visita a hermanos enfermos', 'Visita', 5], ['Visita a los simpatizantes', 'Visita', 10]],
        'Comité de Damas Dorcas' => [['Reunión de Damas Dorcas', 'Reunión', 3], ['Retiro de Damas Dorcas', 'Salida', 8], ['Vigilia de damas', 'Culto', 11]],
        'Comité de Jóvenes' => [['Vigilia de jóvenes', 'Culto', 4], ['Convención juvenil', 'Convención', 7], ['Paseo de jóvenes', 'Salida', 10]],
        'Comité de Red de Familia' => [['Encuentro de familias', 'Evento', 6], ['Escuela para padres', 'Reunión', 9]],
        'Comité de Escuela Dominical' => [['Capacitación de maestros', 'Reunión', 2], ['Clausura de la Escuela Dominical', 'Evento', 11]],
    ];

    /** Lo que se carga para el año siguiente, todavia sin aprobar. */
    private const PROXIMO = [
        'Comité de Alabanza' => [['Culto de aniversario', 'Culto', 2]],
        'Comité de Evangelismo' => [['Campaña de Semana Santa', 'Campaña', 4]],
        'Comité de Damas Dorcas' => [['Día de la mujer', 'Evento', 3]],
        'Comité de Jóvenes' => [['Retiro de jóvenes', 'Salida', 6]],
    ];

    private const TAREAS = ['Reservar el templo', 'Invitar a la congregación', 'Preparar el programa'];

    public function run(): void
    {
        $pastor = User::where('username', 'pastor')->firstOrFail();
        $anio = now('America/Bogota')->year;

        $plan = AnnualPlan::create([
            'site_id' => $pastor->site_id,
            'year' => $anio,
            'approved_by_user_id' => $pastor->id,
            'approved_at' => Carbon::create($anio, 1, 20, 10, 0, 0, 'America/Bogota'),
        ]);

        $vuelta = 0;

        foreach (self::PLAN as $comite => $actividades) {
            foreach ($actividades as [$titulo, $clase, $mes]) {
                $actividad = $this->actividad($comite, $titulo, $clase, Carbon::create($anio, $mes, 8 + ($vuelta % 15), 19, 0, 0, 'America/Bogota'), $plan, $pastor);
                $this->tareas($actividad, $vuelta++);
            }
        }

        $proximo = AnnualPlan::create(['site_id' => $pastor->site_id, 'year' => $anio + 1]);

        foreach (self::PROXIMO as $comite => $actividades) {
            foreach ($actividades as [$titulo, $clase, $mes]) {
                $this->actividad($comite, $titulo, $clase, Carbon::create($anio + 1, $mes, 14, 19, 0, 0, 'America/Bogota'), $proximo, null);
            }
        }
    }

    private function actividad(string $comite, string $titulo, string $clase, Carbon $fecha, AnnualPlan $plan, ?User $aprobo): Activity
    {
        $donde = Committee::where('name', $comite)->firstOrFail();
        $responsable = CommitteeUser::current()->where('committee_id', $donde->id)->value('user_id');

        return Activity::create([
            'site_id' => $donde->site_id,
            'committee_id' => $donde->id,
            'annual_plan_id' => $plan->id,
            'title' => $titulo,
            'description' => "{$titulo} del {$comite}.",
            'scheduled_at' => $fecha,
            'location' => 'Templo central',
            'activity_type_id' => ActivityType::where('site_id', $donde->site_id)->where('name', $clase)->value('id'),
            'status' => $aprobo ? Activity::APPROVED : Activity::PENDING,
            'approved_by_user_id' => $aprobo?->id,
            'approved_at' => $aprobo ? $plan->approved_at : null,
            'proposed_by_user_id' => $responsable ?? User::where('username', 'pastor')->value('id'),
            'proposed_at' => $plan->approved_at?->copy()->subWeeks(3) ?? now(),
        ]);
    }

    /**
     * Las tres tareas de siempre. Lo que ya paso queda cumplido, salvo una de cada cuatro, que va
     * en camino con una sola tarea hecha; lo que viene queda por hacer.
     */
    private function tareas(Activity $actividad, int $vuelta): void
    {
        $hechas = match (true) {
            $actividad->scheduled_at->isFuture() => 0,
            $vuelta % 4 === 3 => 1,
            default => count(self::TAREAS),
        };

        // Primero todas por hacer y despues las cumplidas: con una sola tarea creada, marcarla
        // dejaria la actividad cumplida, y una cumplida ya no vuelve atras.
        $tareas = collect(self::TAREAS)->map(fn (string $descripcion) => $actividad->tasks()->create([
            'site_id' => $actividad->site_id,
            'description' => $descripcion,
        ]));

        $tareas->take($hechas)->each(fn ($tarea, int $i) => $tarea->update([
            'done' => true,
            'done_at' => $actividad->scheduled_at->copy()->subDays(3 - $i),
        ]));
    }
}
