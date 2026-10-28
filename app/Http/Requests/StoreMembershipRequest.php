<?php

namespace App\Http\Requests;

use App\Models\CommitteeMember;
use App\Support\Scope\CurrentScope;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Agregar a una persona ya registrada a la lista de un comité, con su función (RF-084, RF-085).
 * Lo hace cualquier cargo sobre los comités de su ámbito, y el pastor sobre todos.
 */
class StoreMembershipRequest extends FormRequest
{
    public function authorize(): bool
    {
        $scope = app(CurrentScope::class);

        return $scope->user()?->is($this->user()) === true && ($scope->isPastor() || $scope->activeRole() !== null);
    }

    public function rules(): array
    {
        return [
            'member_id' => ['required', 'integer', Rule::exists('members', 'id')->where('site_id', $this->user()->site_id)->where('active', true)],
            'role' => ['required', Rule::in(CommitteeMember::ROLES)],
            'board_position' => ['nullable', 'required_if:role,board', Rule::in(CommitteeMember::BOARD_POSITIONS)],
        ];
    }

    public function messages(): array
    {
        return [
            'member_id.required' => 'Busque a la persona por su nombre y escójala de la lista.',
            'member_id.integer' => 'Busque a la persona por su nombre y escójala de la lista.',
            'member_id.exists' => 'Esa persona no está en el padrón. Si es nueva, pídale al pastor que la registre.',
            'role.required' => 'Escoja qué hace la persona en el comité.',
            'role.in' => 'Escoja qué hace la persona en el comité.',
            'board_position.required_if' => 'Escoja su cargo en la directiva.',
            'board_position.in' => 'Escoja su cargo en la directiva.',
        ];
    }
}
