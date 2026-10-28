<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** La identidad de un comité (RF-083): su logo, su lema y sus objetivos. Todo es opcional. */
class UpdateCommitteeIdentityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('updateIdentity', $this->route('committee'));
    }

    public function rules(): array
    {
        return [
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_logo' => ['boolean'],
            'motto' => ['nullable', 'string', 'max:160'],
            'objectives' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'logo.image' => 'El logo tiene que ser una imagen.',
            'logo.mimes' => 'El logo tiene que ser una imagen JPG, PNG o WEBP.',
            'logo.max' => 'El logo pesa demasiado. Use una imagen de menos de 2 MB.',
            'motto.max' => 'El lema es muy largo. Resúmalo en menos de 160 letras.',
            'objectives.max' => 'Los objetivos son muy largos. Resúmalos en menos de 2.000 letras.',
        ];
    }
}
