<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Rechazar exige el motivo: quien pidio la solicitud lo va a leer (RF-020). */
class RejectApprovalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('resolve', $this->route('approval'));
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'Escriba el motivo. Quien pidió la solicitud lo va a leer.',
            'reason.min' => 'Escriba un motivo un poco más completo, para que se entienda.',
            'reason.max' => 'El motivo es muy largo. Resúmalo en menos de 500 letras.',
        ];
    }
}
