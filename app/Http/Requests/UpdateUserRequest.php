<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('update', $this->route('user'));
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'username' => [
                'required', 'string', 'max:50', 'regex:/^[a-z0-9.]+$/',
                Rule::unique('users', 'username')->ignore($this->route('user')),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Escriba el nombre de la persona.',
            'username.required' => 'Escriba el usuario con el que va a entrar.',
            'username.regex' => 'El usuario lleva solo letras minúsculas, números y puntos. Por ejemplo: maria.gomez',
            'username.unique' => 'Ese usuario ya está tomado. Pruebe agregándole el apellido.',
        ];
    }
}
