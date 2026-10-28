<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\ActivityType;
use App\Models\AnnualPlan;
use App\Models\Committee;
use App\Models\CommitteeUser;
use App\Models\MonthNote;
use App\Models\Schedule;
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
        'Comité de Sonidos' => [['Revisión de los equipos de sonido', 'Reunión', 3], ['Capacitación de sonido', 'Reunión', 8]],
        'Comité de Damas Dorcas' => [['Culto de Damas Dorcas', 'Culto', 3], ['Retiro de Damas Dorcas', 'Salida', 8], ['Vigilia de damas', 'Culto', 11]],
        'Sub-comité Hijas del Rey' => [['Taller de manualidades', 'Reunión', 4]],
        'Sub-comité Mujeres Virtuosas' => [['Tarde de película con los esposos', 'Evento', 6]],
        'Comité de Conquistadores Pentecostales' => [['Culto juvenil', 'Culto', 4], ['Campamento de Conquistadores', 'Salida', 7], ['Paseo de Conquistadores', 'Salida', 10]],
        'Sub-comité Bethel' => [['Torneo bíblico juvenil', 'Evento', 6]],
        'Comité de Misiones y Evangelismo 1' => [['Campaña evangelística', 'Campaña', 3], ['Tarde con amigos', 'Evento', 5]],
        'Comité de Misiones y Evangelismo 2' => [['Día misionero', 'Culto', 4], ['Salida a evangelizar', 'Salida', 7]],
        'Comité de Misiones y Evangelismo 3' => [['Campaña de fin de año', 'Campaña', 11]],
        'Comité de Obra Social' => [['Canasta de amor', 'Evento', 2], ['Taller de calzado', 'Reunión', 5]],
        'Comité de Red de Familia' => [['Encuentro de familias', 'Evento', 6], ['Escuela para padres', 'Reunión', 9]],
        'Comité de Crecimiento Espiritual' => [['Vigilia de crecimiento espiritual', 'Culto', 4], ['Amanecer con Cristo', 'Culto', 8]],
        'Comité de Escuela Dominical' => [['Capacitación de maestros', 'Reunión', 2], ['Clausura de la Escuela Dominical', 'Evento', 11]],
        'Comité de Caballeros' => [['Desayuno de caballeros', 'Reunión', 6]],
        'Comité Decom' => [['Taller de comunicaciones', 'Reunión', 3]],
    ];

    /** Lo que se carga para el año siguiente, todavia sin aprobar. */
    private const PROXIMO = [
        'Comité de Alabanza' => [['Culto de aniversario', 'Culto', 2]],
        'Comité de Misiones y Evangelismo 1' => [['Campaña de Semana Santa', 'Campaña', 4]],
        'Comité de Damas Dorcas' => [['Día de la mujer', 'Evento', 3]],
        'Comité de Conquistadores Pentecostales' => [['Retiro de Conquistadores', 'Salida', 6]],
    ];

    /** El tema y la marca de color de algunas, como en el cronograma de la sede (RF-116). */
    private const MARCAS = [
        'Culto de alabanza' => ['Alabadle al son de bocina', null],
        'Campaña evangelística' => ['Falta uno en el rebaño', 'special_day'],
        'Vigilia de crecimiento espiritual' => ['Y fueron todos llenos del Espíritu Santo', null],
        'Torneo bíblico juvenil' => [null, 'district'],
        'Día misionero' => ['Id y haced discípulos', 'special_day'],
        'Canasta de amor' => [null, 'special_day'],
        'Encuentro de familias' => ['Familias con propósito', 'special_day'],
        'Capacitación de maestros' => [null, 'district'],
        'Vigilia de damas' => ['La cura del corazón roto', null],
        'Campaña de fin de año' => ['Vuelve a casa', 'special_day'],
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
        // Cada actividad en su propio dia del mes: en el cronograma general no se cruzan (D-37).
        $dias = [];

        foreach (self::PLAN as $comite => $actividades) {
            foreach ($actividades as [$titulo, $clase, $mes]) {
                $dias[$mes] = ($dias[$mes] ?? 5) + 3;
                $actividad = $this->actividad($comite, $titulo, $clase, Carbon::create($anio, $mes, $dias[$mes], 19, 0, 0, 'America/Bogota'), $plan, $pastor);
                $this->tareas($actividad, $vuelta++);
            }
        }

        // Las notas del mes, como las escribe el formato de la sede junto al nombre del mes (RF-118).
        foreach ([[5, 'Operación rescate, Conquistadores Pentecostales', 'special_day'], [10, 'Encuentro de esposas de pastores (fecha pendiente)', 'district']] as [$mes, $texto, $marca]) {
            MonthNote::create(['site_id' => $pastor->site_id, 'year' => $anio, 'month' => $mes, 'text' => $texto, 'highlight' => $marca, 'recorded_by_user_id' => $pastor->id]);
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
            'schedule_id' => Schedule::generalOf($donde->site_id, $fecha->year)->id,
            'title' => $titulo,
            'theme' => self::MARCAS[$titulo][0] ?? null,
            'highlight' => self::MARCAS[$titulo][1] ?? null,
            'description' => "{$titulo} del {$comite}.",
            'scheduled_at' => $fecha,
            'ends_at' => $fecha->copy()->addHours(2),
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
