<?php

namespace App\Http\Requests\Concerns;

use App\Models\Member;
use App\Support\People\PersonAge;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

/**
 * Lo que comparten las fichas de personas, de miembro y de simpatizante: la edad, por fecha de
 * nacimiento o anotada con su dia (RF-031, RF-041), y la autorizacion del manejo de datos con su
 * fecha y su medio (RF-061). Un solo lugar, para que las dos fichas no se aparten.
 */
trait PersonFields
{
    /** O la fecha o la edad, nunca las dos: lo decide el modo que escogio quien llena la ficha. */
    protected function preparePersonFields(): void
    {
        $porEdad = $this->input('age_mode') === 'age';
        $autorizo = $this->boolean('data_consent');

        $this->merge([
            'birth_date' => $porEdad ? null : ($this->input('birth_date') ?: null),
            'age_reported' => $porEdad ? $this->input('age_reported') : null,
            'data_consent_at' => $autorizo ? ($this->input('data_consent_at') ?: null) : null,
            'data_consent_means' => $autorizo ? ($this->input('data_consent_means') ?: null) : null,
        ]);
    }

    protected function personRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'age_mode' => ['required', Rule::in(['date', 'age'])],
            'birth_date' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today', 'after:1900-01-01'],
            'age_reported' => ['nullable', 'required_if:age_mode,age', 'integer', 'min:0', 'max:120'],
            'gender' => ['required', Rule::in(['f', 'm'])],
            'phone' => ['nullable', 'string', 'max:30'],
            'neighborhood' => ['nullable', 'string', 'max:80'],
            'data_consent' => ['boolean'],
            'data_consent_at' => ['nullable', 'required_if_accepted:data_consent', 'date_format:Y-m-d', 'before_or_equal:today'],
            'data_consent_means' => ['nullable', 'required_if_accepted:data_consent', Rule::in(Member::CONSENT_MEANS)],
        ];
    }

    protected function personMessages(): array
    {
        return [
            'name.required' => 'Escriba el nombre.',
            'name.max' => 'El nombre es muy largo. Resúmalo en menos de 120 letras.',
            'age_mode.required' => 'Escoja si sabe la fecha de nacimiento o solo la edad.',
            'birth_date.date_format' => 'Escoja la fecha de nacimiento en el calendario.',
            'birth_date.before_or_equal' => 'La fecha de nacimiento no puede ser después de hoy.',
            'birth_date.after' => 'Revise la fecha de nacimiento: es demasiado antigua.',
            'age_reported.required_if' => 'Anote los años que tiene, o escoja la fecha de nacimiento.',
            'age_reported.integer' => 'Escriba los años solo con números.',
            'age_reported.min' => 'Revise la edad.',
            'age_reported.max' => 'Revise la edad: es demasiado alta.',
            'gender.required' => 'Escoja si es hombre o mujer.',
            'gender.in' => 'Escoja si es hombre o mujer.',
            'phone.max' => 'El teléfono es muy largo.',
            'neighborhood.max' => 'El barrio es muy largo.',
            'data_consent_at.required_if_accepted' => 'Anote en qué fecha autorizó el manejo de sus datos.',
            'data_consent_at.date_format' => 'Escoja la fecha de la autorización en el calendario.',
            'data_consent_at.before_or_equal' => 'La fecha de la autorización no puede ser después de hoy.',
            'data_consent_means.required_if_accepted' => 'Escoja cómo dio la autorización: firmando, de palabra o por mensaje.',
            'data_consent_means.in' => 'Escoja cómo dio la autorización: firmando, de palabra o por mensaje.',
        ];
    }

    /** La edad que tendria con lo escrito en el formulario, para las reglas que dependen de ella. */
    protected function ageFromForm(): ?int
    {
        $nacimiento = $this->input('birth_date') ? Carbon::parse($this->input('birth_date')) : null;
        $edad = $this->input('age_reported');

        return PersonAge::of($nacimiento, $edad === null || $edad === '' ? null : (int) $edad, now());
    }

    /**
     * Lo comun que se guarda. La edad anotada lleva el dia en que se anoto; si al corregir la
     * ficha la edad no cambio, se conserva el dia de antes, para que no rejuvenezca.
     */
    protected function personData(?CarbonInterface $anotadaAntes = null, ?int $edadAntes = null): array
    {
        $edad = $this->validated('age_reported');
        $edad = $edad === null ? null : (int) $edad;
        $autorizo = $this->boolean('data_consent');

        return [
            'name' => trim($this->validated('name')),
            'birth_date' => $this->validated('birth_date'),
            'age_reported' => $edad,
            'age_reported_at' => $edad === null ? null : ($anotadaAntes && $edadAntes === $edad ? $anotadaAntes : now('America/Bogota')->toDateString()),
            'gender' => $this->validated('gender'),
            'phone' => $this->validated('phone'),
            'neighborhood' => $this->validated('neighborhood'),
            'data_consent' => $autorizo,
            'data_consent_at' => $autorizo ? $this->validated('data_consent_at') : null,
            'data_consent_means' => $autorizo ? $this->validated('data_consent_means') : null,
        ];
    }
}
