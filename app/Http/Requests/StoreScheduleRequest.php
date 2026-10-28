<?php

namespace App\Http\Requests;

use App\Models\Schedule;
use App\Support\Activities\ScheduleCards;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/** Un cronograma interno nuevo (HU-27 escenario 1): su nombre y su periodo, dentro de un mismo año. */
class StoreScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('create', Schedule::class);
    }

    public function rules(): array
    {
        $hoy = now(ScheduleCards::ZONE)->year;

        return [
            'name' => ['required', 'string', 'max:80'],
            'starts_on' => ['required', 'date_format:Y-m-d', 'after_or_equal:'.$hoy.'-01-01', 'before_or_equal:'.($hoy + 1).'-12-31'],
            'ends_on' => ['required', 'date_format:Y-m-d', 'after_or_equal:starts_on'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if (! $validator->errors()->hasAny(['starts_on', 'ends_on']) && substr($this->input('ends_on'), 0, 4) !== substr($this->input('starts_on'), 0, 4)) {
                    $validator->errors()->add('ends_on', 'El cronograma termina el mismo año en que empieza. Para el año siguiente, cree otro.');
                }
            },
        ];
    }

    public function messages(): array
    {
        $hoy = now(ScheduleCards::ZONE)->year;
        $anios = "Escoja una fecha de {$hoy} o de ".($hoy + 1).'.';

        return [
            'name.required' => 'Escriba el nombre del cronograma. Por ejemplo: Módulo 1.',
            'name.max' => 'El nombre es muy largo. Resúmalo en menos de 80 letras.',
            'starts_on.required' => 'Escoja la fecha en que empieza.',
            'starts_on.date_format' => 'Escoja la fecha en el calendario.',
            'starts_on.after_or_equal' => $anios,
            'starts_on.before_or_equal' => $anios,
            'ends_on.required' => 'Escoja la fecha en que termina.',
            'ends_on.date_format' => 'Escoja la fecha en el calendario.',
            'ends_on.after_or_equal' => 'La fecha en que termina debe ser después de la fecha en que empieza.',
        ];
    }
}
