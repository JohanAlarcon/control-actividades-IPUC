<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Agregar una tarea a una actividad del propio comite. */
class StoreActivityTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('update', $this->route('activity'));
    }

    public function rules(): array
    {
        return [
            'description' => ['required', 'string', 'max:150'],
            'assigned_to' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'description.required' => 'Escriba qué hay que hacer. Por ejemplo: Reservar el templo.',
            'description.max' => 'La tarea es muy larga. Resúmala en menos de 150 letras.',
            'assigned_to.max' => 'El nombre es muy largo. Resúmalo en menos de 100 letras.',
        ];
    }
}
