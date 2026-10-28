<?php

namespace App\Http\Requests;

use App\Support\Activities\LocalTime;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Mover una sola fecha de un cronograma interno (CL-30): a otro día o a otra hora, dentro del
 * periodo del cronograma. Las demás fechas de su serie siguen como estaban.
 */
class MoveInternalActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        $actividad = $this->route('activity');

        return $actividad->cancelled_at === null && (bool) $this->user()?->can('addActivity', $actividad->schedule);
    }

    public function rules(): array
    {
        $cronograma = $this->route('activity')->schedule;

        return [
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:'.$cronograma->starts_on->toDateString(), 'before_or_equal:'.$cronograma->ends_on->toDateString()],
            'time' => ['required', 'date_format:H:i'],
            'ends' => ['required', 'date_format:H:i'],
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

    public function scheduledAt(): CarbonInterface
    {
        return LocalTime::of($this->input('date'), $this->input('time'));
    }

    public function endsAt(): CarbonInterface
    {
        return LocalTime::of($this->input('date'), $this->input('ends'));
    }

    public function messages(): array
    {
        $cronograma = $this->route('activity')->schedule;
        $periodo = 'La fecha debe estar dentro del cronograma: del '.$this->dia($cronograma->starts_on).' al '.$this->dia($cronograma->ends_on).'.';

        return [
            'date.required' => 'Escoja la fecha.',
            'date.date_format' => 'Escoja la fecha en el calendario.',
            'date.after_or_equal' => $periodo,
            'date.before_or_equal' => $periodo,
            'time.required' => 'Escoja la hora de inicio.',
            'time.date_format' => 'Escoja la hora en el reloj.',
            'ends.required' => 'Escoja la hora en que termina.',
            'ends.date_format' => 'Escoja la hora en el reloj.',
        ];
    }

    private function dia(CarbonInterface $fecha): string
    {
        return $fecha->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
    }
}
