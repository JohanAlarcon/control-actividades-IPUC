<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ConfirmsHandover;
use App\Models\CommitteeUser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreCommitteeUserRequest extends FormRequest
{
    use ConfirmsHandover;

    public function authorize(): bool
    {
        return (bool) $this->user()?->can('is-pastor');
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'role' => ['required', Rule::in([
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
            fn (Validator $validator) => $this->confirmHandover($validator, $this->route('committee')?->id, $this->integer('user_id')),
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Escoja a quién le va a asignar el cargo.',
            'user_id.exists' => 'Esa persona ya no tiene cuenta.',
            'role.required' => 'Escoja qué cargo va a tener.',
            'role.in' => 'Ese cargo no existe.',
        ];
    }
}
