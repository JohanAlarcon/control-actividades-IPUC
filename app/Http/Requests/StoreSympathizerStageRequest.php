<?php

namespace App\Http\Requests;

use App\Models\Sympathizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Anotar la etapa siguiente del camino, con su fecha (RF-042). Una etapa no se alcanza sin las anteriores. */
class StoreSympathizerStageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('follow', $this->route('sympathizer'));
    }

    public function rules(): array
    {
        /** @var Sympathizer $persona */
        $persona = $this->route('sympathizer');
        $ultima = $persona->stages()->max('reached_at') ?? $persona->first_contact_at->toDateString();

        return [
            'stage' => ['required', 'integer', Rule::in([$persona->stage + 1]), 'max:'.Sympathizer::LAST_STAGE],
            'reached_at' => ['required', 'date_format:Y-m-d', 'before_or_equal:today', 'after_or_equal:'.$ultima],
        ];
    }

    public function messages(): array
    {
        return [
            'stage.in' => 'Las etapas se anotan en orden: primero la que sigue.',
            'stage.max' => 'Ya recorrió todas las etapas.',
            'reached_at.required' => 'Anote la fecha en que alcanzó esta etapa.',
            'reached_at.date_format' => 'Escoja la fecha en el calendario.',
            'reached_at.before_or_equal' => 'La fecha no puede ser después de hoy.',
            'reached_at.after_or_equal' => 'La fecha no puede ser anterior a la de la etapa que ya tenía.',
        ];
    }
}
