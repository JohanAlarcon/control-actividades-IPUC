<?php

namespace App\Http\Requests;

use App\Models\Activity;
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
        $hoy = now(self::ZONE);

        return [
            'title' => ['required', 'string', 'max:150'],
            'activity_type_id' => ['required', 'integer', Rule::exists('activity_types', 'id')->where('site_id', $this->user()->site_id)->where('active', true)],
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:'.$hoy->toDateString(), 'before_or_equal:'.$hoy->copy()->endOfYear()->toDateString()],
            'time' => ['required', 'date_format:H:i'],
            'location' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'budget' => ['nullable', 'integer', 'min:0', 'max:999999999'],
        ];
    }

    /** Una fecha de hoy con una hora que ya pasó tambien es pasada (CL-07). */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if (! $validator->errors()->hasAny(['date', 'time']) && $this->scheduledAt()->isPast()) {
                    $validator->errors()->add('time', 'Esa hora de hoy ya pasó. Escoja una hora más tarde, u otro día.');
                }
            },
        ];
    }

    /** La fecha y la hora que escogió la persona, en hora de Colombia, listas para guardar. */
    public function scheduledAt(): Carbon
    {
        return Carbon::createFromFormat('Y-m-d H:i', $this->input('date').' '.$this->input('time'), self::ZONE)
            ->setTimezone(config('app.timezone'));
    }

    public function messages(): array
    {
        $anio = now(self::ZONE)->year;

        return [
            'title.required' => 'Escriba el nombre de la actividad.',
            'title.max' => 'El nombre es muy largo. Resúmalo en menos de 150 letras.',
            'activity_type_id.required' => 'Escoja qué clase de actividad es.',
            'activity_type_id.integer' => 'Escoja qué clase de actividad es.',
            'activity_type_id.exists' => 'Escoja una de las clases de actividad que se ofrecen.',
            'date.required' => 'Escoja la fecha.',
            'date.date_format' => 'Escoja la fecha en el calendario.',
            'date.after_or_equal' => 'Esa fecha ya pasó. Escoja una de hoy en adelante.',
            'date.before_or_equal' => "La fecha debe ser de este año, {$anio}. Lo del año que viene va en el plan del próximo año.",
            'time.required' => 'Escoja la hora.',
            'time.date_format' => 'Escoja la hora en el reloj.',
            'location.max' => 'El lugar es muy largo. Resúmalo en menos de 150 letras.',
            'description.max' => 'La descripción es muy larga. Resúmala en menos de 2.000 letras.',
            'budget.integer' => 'Escriba el presupuesto solo con números. Por ejemplo: 350.000.',
            'budget.min' => 'El presupuesto no puede ser negativo.',
            'budget.max' => 'Revise el presupuesto: es demasiado alto.',
        ];
    }
}
