<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ConfirmsHandover;
use App\Models\CommitteeUser;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreUserRequest extends FormRequest
{
    use ConfirmsHandover;

    public function authorize(): bool
    {
        return (bool) $this->user()?->can('create', User::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'username' => ['nullable', 'string', 'max:50', 'regex:/^[a-z0-9.]+$/', Rule::unique('users', 'username')],
            'committee_id' => ['nullable', 'integer', 'exists:committees,id'],
            'role' => ['nullable', 'required_with:committee_id', Rule::in([
                CommitteeUser::PRESIDENT,
                CommitteeUser::SECRETARY,
                CommitteeUser::TREASURER,
                CommitteeUser::LEADER,
            ])],
            'handover' => ['nullable', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [
            fn (Validator $validator) => $this->confirmHandover($validator, $this->integer('committee_id') ?: null, null),
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Escriba el nombre de la persona.',
            'name.max' => 'El nombre es demasiado largo.',
            'username.regex' => 'El usuario lleva solo letras minúsculas, números y puntos. Por ejemplo: maria.gomez',
            'username.unique' => 'Ese usuario ya está tomado. Pruebe agregándole el apellido.',
            'username.max' => 'El usuario es demasiado largo.',
            'committee_id.exists' => 'Ese comité ya no existe.',
            'role.required_with' => 'Escoja qué cargo va a tener en ese comité.',
            'role.in' => 'Ese cargo no existe.',
        ];
    }
}
