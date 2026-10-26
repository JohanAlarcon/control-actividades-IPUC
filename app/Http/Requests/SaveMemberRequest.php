<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\PersonFields;
use App\Models\Member;
use App\Support\Scope\CurrentScope;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Registrar o corregir a un miembro (HU-10). Nunca se bloquea por un dato que la congregacion no
 * tiene: sin fecha ni edad, basta con confirmar la mayoria de edad (RF-032, principio IV).
 */
class SaveMemberRequest extends FormRequest
{
    use PersonFields;

    public function authorize(): bool
    {
        $miembro = $this->route('member');

        return (bool) ($miembro instanceof Member
            ? $this->user()?->can('update', $miembro)
            : $this->user()?->can('create', Member::class));
    }

    protected function prepareForValidation(): void
    {
        $this->preparePersonFields();
        $this->merge(['baptized_at' => $this->boolean('baptized') ? ($this->input('baptized_at') ?: null) : null]);
    }

    public function rules(): array
    {
        $scope = app(CurrentScope::class);

        return $this->personRules() + [
            'marital_status' => ['required', Rule::in(Member::MARITAL_STATUSES)],
            'adult_confirmed' => ['boolean'],
            'baptized' => ['boolean'],
            'baptized_at' => ['nullable', 'required_if_accepted:baptized', 'date_format:Y-m-d', 'before_or_equal:today'],
            'joined_at' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
            'committee_id' => [$scope->isPastor() ? 'nullable' : 'required', 'integer', Rule::in($scope->committeeIds())],
        ];
    }

    /** Las reglas que dependen de la edad (RF-032) y de las dos fechas a la vez. */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->hasAny(['birth_date', 'age_reported', 'marital_status', 'baptized_at'])) {
                    return;
                }

                $edad = $this->ageFromForm();
                $soloAdultos = in_array($this->input('marital_status'), Member::ADULT_ONLY, true);

                if ($soloAdultos && $edad !== null && $edad < 18) {
                    $validator->errors()->add('marital_status', 'Una persona menor de dieciocho años no puede figurar casada, viuda, separada ni en unión libre. Revise la fecha de nacimiento o el estado civil.');
                }

                if ($soloAdultos && $edad === null && ! $this->boolean('adult_confirmed')) {
                    $validator->errors()->add('adult_confirmed', 'Confirme que la persona es mayor de dieciocho años. No hace falta la fecha: basta con que usted lo confirme.');
                }

                if ($this->input('baptized_at') && $this->input('birth_date') && $this->input('baptized_at') < $this->input('birth_date')) {
                    $validator->errors()->add('baptized_at', 'La fecha de bautismo no puede ser anterior a la de nacimiento.');
                }
            },
        ];
    }

    /** Lo que se guarda: la ficha, la confirmacion de mayoria de edad con su dia, y si queda por completar. */
    public function memberData(?Member $antes = null): array
    {
        $datos = $this->personData($antes?->age_reported_at, $antes?->age_reported);
        $sinEdad = $datos['birth_date'] === null && $datos['age_reported'] === null;
        $confirmado = $sinEdad && in_array($this->validated('marital_status'), Member::ADULT_ONLY, true) && $this->boolean('adult_confirmed');

        return $datos + [
            'marital_status' => $this->validated('marital_status'),
            'baptized' => $this->boolean('baptized'),
            'baptized_at' => $this->boolean('baptized') ? $this->validated('baptized_at') : null,
            'joined_at' => $this->validated('joined_at'),
            'committee_id' => $this->validated('committee_id'),
            'adult_confirmed' => $confirmado,
            'adult_confirmed_at' => $confirmado ? ($antes?->adult_confirmed_at ?? now('America/Bogota')->toDateString()) : null,
            'profile_pending' => $sinEdad,
        ];
    }

    public function messages(): array
    {
        return $this->personMessages() + [
            'marital_status.required' => 'Escoja el estado civil.',
            'marital_status.in' => 'Escoja el estado civil.',
            'baptized_at.required_if_accepted' => 'Anote la fecha del bautismo en el nombre de Jesucristo.',
            'baptized_at.date_format' => 'Escoja la fecha del bautismo en el calendario.',
            'baptized_at.before_or_equal' => 'La fecha de bautismo no puede ser después de hoy.',
            'joined_at.date_format' => 'Escoja la fecha de ingreso en el calendario.',
            'joined_at.before_or_equal' => 'La fecha de ingreso no puede ser después de hoy.',
            'committee_id.required' => 'Escoja el comité, sub-comité o equipo de la persona.',
            'committee_id.in' => 'Escoja uno de los comités de la lista.',
            'committee_id.integer' => 'Escoja uno de los comités de la lista.',
        ];
    }
}
