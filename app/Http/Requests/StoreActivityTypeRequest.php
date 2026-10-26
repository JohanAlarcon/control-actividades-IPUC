<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Agregar una clase de actividad. Solo el pastor (D-25), y sin repetir una que ya existe. */
class StoreActivityTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('is-pastor');
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['name' => trim((string) $this->input('name'))]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:60', Rule::unique('activity_types', 'name')->where('site_id', $this->user()->site_id)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Escriba el nombre de la clase. Por ejemplo: Ayuno.',
            'name.max' => 'El nombre es muy largo. Resúmalo en menos de 60 letras.',
            'name.unique' => 'Ya hay una clase con ese nombre. Si está desactivada, actívela de nuevo.',
        ];
    }
}
