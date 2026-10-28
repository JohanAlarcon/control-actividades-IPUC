<?php

namespace App\Http\Requests;

use App\Models\Activity;
use App\Models\CommitteeUser;
use App\Support\Activities\ScheduleConflict;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/** Proponer una actividad (HU-05). Cada mensaje dice exactamente qué campo corregir (escenario 4). */
class StoreActivityRequest extends FormRequest
{
    /** «El año en curso» y «hoy» se cuentan en hora de Colombia, la configure como la configure el servidor (S-04). */
    private const ZONE = 'America/Bogota';

    public function authorize(): bool
    {
        if ($this->route('year') !== null) {
            $hoy = now(self::ZONE)->year;
            abort_unless(in_array((int) $this->route('year'), [$hoy, $hoy + 1], true), 404);
        }

        return (bool) $this->user()?->can('create', Activity::class);
    }

    /**
     * El presupuesto llega como la persona lo escribe: «350.000» o «$ 350.000» son 350000. Solo se
     * quitan el signo, los puntos y los espacios; una letra se queda, para que se le señale.
     */
    protected function prepareForValidation(): void
    {
        $presupuesto = preg_replace('/[\s$.\x{00A0}]/u', '', (string) $this->input('budget'));

        $this->merge(['budget' => $presupuesto === '' ? null : $presupuesto]);
    }

    public function rules(): array
    {
        $anio = $this->targetYear();
        $desde = max(now(self::ZONE)->toDateString(), "{$anio}-01-01");

        return [
            'title' => ['required', 'string', 'max:150'],
            'theme' => ['nullable', 'string', 'max:120'],
            'highlight' => ['nullable', Rule::in(Activity::HIGHLIGHTS)],
            'activity_type_id' => ['required', 'integer', Rule::exists('activity_types', 'id')->where('site_id', $this->user()->site_id)->where('active', true)],
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:'.$desde, 'before_or_equal:'."{$anio}-12-31"],
            'time' => ['required', 'date_format:H:i'],
            'ends' => ['required', 'date_format:H:i'],
            'location' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'budget' => ['nullable', 'integer', 'min:0', 'max:999999999'],
        ];
    }

    /**
     * Una fecha de hoy con una hora que ya paso tambien es pasada (CL-07); la hora de fin va
     * despues de la de inicio. Y si todo esta bien pero el horario se cruza con otra actividad del
     * cronograma general, no se guarda: se dice cual es y de que comite (RF-028, D-37).
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if (! $validator->errors()->hasAny(['date', 'time']) && $this->scheduledAt()->isPast()) {
                    $validator->errors()->add('time', 'Esa hora de hoy ya pasó. Escoja una hora más tarde, u otro día.');
                }
            },
            function (Validator $validator) {
                if (! $validator->errors()->hasAny(['date', 'time', 'ends']) && $this->endsAt()->lessThanOrEqualTo($this->scheduledAt())) {
                    $validator->errors()->add('ends', 'La hora de fin debe ser después de la hora de inicio.');
                }
            },
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $choques = ScheduleConflict::with($this->scheduledAt(), $this->endsAt());

                if ($choques !== []) {
                    $validator->errors()->add('conflict', ScheduleConflict::describe($choques));
                }
            },
        ];
    }

    /** El año al que va la actividad: el del plan, si se carga al plan (HU-08); si no, el año en curso. */
    public function targetYear(): int
    {
        return (int) ($this->route('year') ?? now(self::ZONE)->year);
    }

    /** Lo que se guarda: los datos del formulario y quien la propone, desde su cargo activo. */
    public function activityData(CommitteeUser $cargo): array
    {
        return [
            'site_id' => $cargo->site_id,
            'committee_id' => $cargo->committee_id,
            'title' => $this->validated('title'),
            'theme' => $this->validated('theme'),
            'highlight' => $this->validated('highlight'),
            'description' => $this->validated('description'),
            'scheduled_at' => $this->scheduledAt(),
            'ends_at' => $this->endsAt(),
            'location' => $this->validated('location'),
            'activity_type_id' => $this->validated('activity_type_id'),
            'budget_amount' => $this->validated('budget'),
            'proposed_by_user_id' => $cargo->user_id,
            'proposed_at' => now(),
        ];
    }

    /** La fecha y la hora que escogió la persona, en hora de Colombia, listas para guardar. */
    public function scheduledAt(): Carbon
    {
        return $this->momento($this->input('time'));
    }

    /** La hora de fin, el mismo día (D-37). */
    public function endsAt(): Carbon
    {
        return $this->momento($this->input('ends'));
    }

    private function momento(string $hora): Carbon
    {
        return Carbon::createFromFormat('Y-m-d H:i', $this->input('date').' '.$hora, self::ZONE)
            ->setTimezone(config('app.timezone'));
    }

    public function messages(): array
    {
        $anio = $this->targetYear();
        $alPlan = $this->route('year') !== null;
        $delPlan = "La fecha debe ser del {$anio}, el año del plan.";

        return [
            'title.required' => 'Escriba el nombre de la actividad.',
            'title.max' => 'El nombre es muy largo. Resúmalo en menos de 150 letras.',
            'theme.max' => 'El tema es muy largo. Resúmalo en menos de 120 letras.',
            'highlight.in' => 'Escoja una de las formas de destacarla que se ofrecen.',
            'activity_type_id.required' => 'Escoja qué clase de actividad es.',
            'activity_type_id.integer' => 'Escoja qué clase de actividad es.',
            'activity_type_id.exists' => 'Escoja una de las clases de actividad que se ofrecen.',
            'date.required' => 'Escoja la fecha.',
            'date.date_format' => 'Escoja la fecha en el calendario.',
            'date.after_or_equal' => $alPlan && $anio > now(self::ZONE)->year ? $delPlan : 'Esa fecha ya pasó. Escoja una de hoy en adelante.',
            'date.before_or_equal' => $alPlan ? $delPlan : "La fecha debe ser de este año, {$anio}. Lo del año que viene va en el plan del próximo año.",
            'time.required' => 'Escoja la hora de inicio.',
            'time.date_format' => 'Escoja la hora en el reloj.',
            'ends.required' => 'Escoja la hora en que termina.',
            'ends.date_format' => 'Escoja la hora en el reloj.',
            'location.max' => 'El lugar es muy largo. Resúmalo en menos de 150 letras.',
            'description.max' => 'La descripción es muy larga. Resúmala en menos de 2.000 letras.',
            'budget.integer' => 'Escriba el presupuesto solo con números. Por ejemplo: 350.000.',
            'budget.min' => 'El presupuesto no puede ser negativo.',
            'budget.max' => 'Revise el presupuesto: es demasiado alto.',
        ];
    }
}
