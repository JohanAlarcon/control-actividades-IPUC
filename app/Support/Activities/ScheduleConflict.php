<?php

namespace App\Support\Activities;

use App\Models\Activity;
use Carbon\CarbonInterface;
use Illuminate\Validation\ValidationException;

/**
 * El cruce en el cronograma general (RF-028, D-37, CL-08): dos actividades el mismo dia con
 * horarios que se sobreponen, de cualquier comite, sin importar el lugar (P-02). No es un aviso:
 * se impide. Las contiguas —una termina a la hora en que empieza la otra— no se cruzan. Mira el
 * cronograma compartido, asi que de lo ajeno solo ve los seis datos, nunca el presupuesto.
 */
class ScheduleConflict
{
    /** Lo que ya tiene su horario tomado al aprobar: lo aprobado, lo que va en camino y lo cumplido. */
    private const TAKEN = [Activity::APPROVED, Activity::IN_PROGRESS, Activity::COMPLETED];

    /**
     * Las actividades que se sobreponen con ese horario. Al proponer cuenta todo lo que no se
     * rechazo, porque lo que espera aprobacion tambien aparta su hora.
     *
     * @return array<int, array{title: string, committee: string, at: CarbonInterface, until: CarbonInterface}>
     */
    public static function with(CarbonInterface $desde, CarbonInterface $hasta, ?int $salvo = null, bool $soloAprobadas = false): array
    {
        return Activity::forSharedCalendar()
            ->where('activities.scheduled_at', '<', $hasta)
            ->where('activities.ends_at', '>', $desde)
            ->when($soloAprobadas,
                fn ($consulta) => $consulta->whereIn('activities.status', self::TAKEN),
                fn ($consulta) => $consulta->where('activities.status', '!=', Activity::REJECTED))
            ->when($salvo, fn ($consulta) => $consulta->where('activities.id', '!=', $salvo))
            ->orderBy('activities.scheduled_at')
            ->get()
            ->map(fn (Activity $otra) => [
                'title' => $otra->title,
                'committee' => $otra->committee_name,
                'at' => $otra->scheduled_at,
                'until' => $otra->ends_at,
            ])
            ->all();
    }

    /** El cruce en palabras: que actividad es, de que comite y de que hora a que hora. */
    public static function describe(array $choques): string
    {
        $partes = array_map(
            fn (array $choque) => "«{$choque['title']}» de {$choque['committee']}, de ".self::hora($choque['at']).' a '.self::hora($choque['until']),
            $choques,
        );

        return 'Ese horario se cruza con '.implode('; ', $partes).'. En el cronograma general dos actividades no se pueden cruzar: escoja otra hora u otro día, o coordínelo con ese comité.';
    }

    /**
     * Antes de aprobar se revisa otra vez, contra lo ya aprobado: dos propuestas que llegaron al
     * mismo tiempo no pueden quedar aprobadas las dos. Quien llama lo hace dentro de un bloqueo.
     */
    public static function guardApproval(Activity $actividad): void
    {
        $choques = self::with($actividad->scheduled_at, $actividad->ends_at, $actividad->id, soloAprobadas: true);

        if ($choques !== []) {
            throw ValidationException::withMessages(['approval' => "«{$actividad->title}» no se puede aprobar. ".self::describe($choques)]);
        }
    }

    private static function hora(CarbonInterface $momento): string
    {
        return $momento->copy()->setTimezone(ScheduleCards::ZONE)->locale('es')->isoFormat('h:mm a');
    }
}
