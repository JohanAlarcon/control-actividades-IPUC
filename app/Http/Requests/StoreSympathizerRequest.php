<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\PersonFields;
use App\Models\Committee;
use App\Models\Sympathizer;
use App\Support\Scope\CurrentScope;
use App\Support\Sympathizers\DuplicateCheck;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/** Registrar a alguien que visito la iglesia (HU-13). Lo puede cualquier cargo (RF-040). */
class StoreSympathizerRequest extends FormRequest
{
    use PersonFields;

    public function authorize(): bool
    {
        return (bool) $this->user()?->can('create', Sympathizer::class);
    }

    protected function prepareForValidation(): void
    {
        $this->preparePersonFields();
    }

    public function rules(): array
    {
        return $this->personRules() + [
            'arrival_source' => ['required', Rule::in(Sympathizer::ARRIVAL_SOURCES)],
            'invited_by_name' => ['nullable', 'string', 'max:120'],
            'first_contact_at' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'committee_id' => $this->choosesCommittee()
                ? ['required', 'integer', Rule::in(Committee::forAssignment()->pluck('id'))]
                : ['prohibited'],
        ];
    }

    /** BIS y el pastor escogen el comite que la acompaña; un comite registra a los suyos (RF-040). */
    public function choosesCommittee(): bool
    {
        $scope = app(CurrentScope::class);

        return $scope->isPastor() || $scope->isReception();
    }

    /** El comite al que queda asignada: el que se escogio, o el del cargo que la registra. */
    public function assignedCommittee(): int
    {
        return $this->choosesCommittee() ? (int) $this->validated('committee_id') : app(CurrentScope::class)->activeRole()->committee_id;
    }

    /** Si ya hay alguien con ese nombre y ese telefono, se avisa antes de guardar (CL-09). */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty() || $this->boolean('confirm_duplicate')) {
                    return;
                }

                if (DuplicateCheck::exists($this->input('name'), $this->input('phone'))) {
                    $telefono = filled($this->input('phone')) ? ' y ese teléfono' : '';
                    $validator->errors()->add('duplicate', "Ya hay una persona registrada con el nombre «{$this->input('name')}»{$telefono}. Si es la misma, no hace falta registrarla otra vez.");
                }
            },
        ];
    }

    public function sympathizerData(): array
    {
        return $this->personData() + [
            'arrival_source' => $this->validated('arrival_source'),
            'invited_by_name' => $this->validated('invited_by_name'),
            'first_contact_at' => $this->validated('first_contact_at'),
        ];
    }

    public function messages(): array
    {
        return $this->personMessages() + [
            'arrival_source.required' => 'Escoja cómo llegó a la iglesia.',
            'arrival_source.in' => 'Escoja cómo llegó a la iglesia.',
            'invited_by_name.max' => 'El nombre de quien lo invitó es muy largo.',
            'first_contact_at.required' => 'Anote la fecha en que vino por primera vez.',
            'first_contact_at.date_format' => 'Escoja la fecha del primer contacto en el calendario.',
            'first_contact_at.before_or_equal' => 'La fecha del primer contacto no puede ser después de hoy.',
            'committee_id.required' => 'Escoja el comité que va a acompañar a la persona.',
            'committee_id.integer' => 'Escoja uno de los comités de la lista.',
            'committee_id.in' => 'Escoja uno de los comités de la lista.',
            'committee_id.prohibited' => 'La persona queda en la lista de su comité: no hace falta escoger otro.',
        ];
    }
}
