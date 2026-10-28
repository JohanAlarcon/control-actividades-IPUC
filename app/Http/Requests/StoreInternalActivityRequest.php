<?php

namespace App\Http\Requests;

use App\Support\Activities\LocalTime;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Una actividad de un cronograma interno (HU-27 escenario 2): dentro del periodo del cronograma.
 * No se revisan cruces: lo interno se puede cruzar con todo (RF-087).
 */
class StoreInternalActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('addActivity', $this->route('schedule'));
    }

    public function rules(): array
    {
        $cronograma = $this->route('schedule');

        return [
            'title' => ['required', 'string', 'max:150'],
            'theme' => ['nullable', 'string', 'max:120'],
            'activity_type_id' => ['required', 'integer', Rule::exists('activity_types', 'id')->where('site_id', $this->user()->site_id)->where('active', true)],
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:'.$cronograma->starts_on->toDateString(), 'before_or_equal:'.$cronograma->ends_on->toDateString()],
            'time' => ['required', 'date_format:H:i'],
            'ends' => ['required', 'date_format:H:i'],
            'location' => ['nullable', 'string', 'max:150'],
            'responsible' => ['nullable', 'string', 'max:80'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if (! $validator->errors()->hasAny(['date', 'time', 'ends']) && $this->endsAt()->lessThanOrEqualTo($this->scheduledAt())) {
                    $validator->errors()->add('ends', 'La hora de fin debe ser después de la hora de inicio.');
                }
            },
        ];
    }

    public function scheduledAt(): Carbon
    {
        return LocalTime::of($this->input('date'), $this->input('time'));
    }

    public function endsAt(): Carbon
    {
        return LocalTime::of($this->input('date'), $this->input('ends'));
    }

    public function messages(): array
    {
        $cronograma = $this->route('schedule');
        $periodo = 'La fecha debe estar dentro del cronograma: del '.$this->dia($cronograma->starts_on).' al '.$this->dia($cronograma->ends_on).'.';

        return [
            'title.required' => 'Escriba el nombre de la actividad.',
            'title.max' => 'El nombre es muy largo. Resúmalo en menos de 150 letras.',
            'theme.max' => 'El tema es muy largo. Resúmalo en menos de 120 letras.',
            'activity_type_id.required' => 'Escoja qué clase de actividad es.',
            'activity_type_id.integer' => 'Escoja qué clase de actividad es.',
            'activity_type_id.exists' => 'Escoja una de las clases de actividad que se ofrecen.',
            'date.required' => 'Escoja la fecha.',
            'date.date_format' => 'Escoja la fecha en el calendario.',
            'date.after_or_equal' => $periodo,
            'date.before_or_equal' => $periodo,
            'time.required' => 'Escoja la hora de inicio.',
            'time.date_format' => 'Escoja la hora en el reloj.',
            'ends.required' => 'Escoja la hora en que termina.',
            'ends.date_format' => 'Escoja la hora en el reloj.',
            'location.max' => 'El lugar es muy largo. Resúmalo en menos de 150 letras.',
            'responsible.max' => 'El nombre de quien la dirige es muy largo. Resúmalo en menos de 80 letras.',
        ];
    }

    private function dia(CarbonInterface $fecha): string
    {
        return $fecha->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
    }
}
