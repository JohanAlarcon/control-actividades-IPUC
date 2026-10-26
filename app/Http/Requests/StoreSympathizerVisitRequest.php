<?php

namespace App\Http\Requests;

use App\Models\SympathizerVisit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Anotar una visita, llamada, estudio o asistencia al culto, con su fecha y una nota (RF-044). */
class StoreSympathizerVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('follow', $this->route('sympathizer'));
    }

    public function rules(): array
    {
        return [
            'visited_at' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'type' => ['required', Rule::in(SympathizerVisit::TYPES)],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'visited_at.required' => 'Anote la fecha.',
            'visited_at.date_format' => 'Escoja la fecha en el calendario.',
            'visited_at.before_or_equal' => 'La fecha no puede ser después de hoy.',
            'type.required' => 'Escoja qué fue: visita, llamada, estudio o el culto.',
            'type.in' => 'Escoja qué fue: visita, llamada, estudio o el culto.',
            'note.max' => 'La nota es muy larga. Resúmala en menos de 500 letras.',
        ];
    }
}
