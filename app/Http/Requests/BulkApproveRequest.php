<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Aprobar varias de una vez. Solo el pastor, que es quien aprueba (RF-022, D-24). */
class BulkApproveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('is-pastor');
    }

    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:2', 'max:50'],
            'ids.*' => ['integer', 'distinct'],
        ];
    }

    public function messages(): array
    {
        return [
            'ids.required' => 'Escoja las solicitudes que quiere aprobar.',
            'ids.min' => 'Para aprobar de a una, use el botón de cada tarjeta.',
            'ids.max' => 'Son demasiadas para una sola vez. Apruébelas en grupos de 50 o menos.',
            'ids.*.integer' => 'Una de las solicitudes no se reconoce. Vuelva a abrir la bandeja.',
            'ids.*.distinct' => 'Una solicitud aparece repetida. Vuelva a abrir la bandeja.',
        ];
    }
}
