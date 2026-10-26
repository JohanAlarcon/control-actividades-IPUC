<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Cambiar el nombre de una clase, o desactivarla y activarla. Solo el pastor (D-25). */
class UpdateActivityTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('is-pastor');
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('name')) {
            $this->merge(['name' => trim((string) $this->input('name'))]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:60',
                Rule::unique('activity_types', 'name')->where('site_id', $this->user()->site_id)->ignore($this->route('activityType')),
            ],
            'active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Escriba el nombre de la clase.',
            'name.max' => 'El nombre es muy largo. Resúmalo en menos de 60 letras.',
            'name.unique' => 'Ya hay otra clase con ese nombre.',
        ];
    }
}
