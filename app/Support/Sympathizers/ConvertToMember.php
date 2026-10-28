<?php

namespace App\Support\Sympathizers;

use App\Models\Member;
use App\Models\Sympathizer;

/**
 * El paso a miembro, una vez lo aprueba el pastor (RF-045, RN-06). La persona entra a la lista de
 * miembros conservando su fecha de bautismo, y la ficha de simpatizante queda enlazada —nunca se
 * borra—, con su primer contacto y todo su camino (CL-10). La ficha nueva queda por completar: el
 * estado civil lo anota el pastor, y cada comite la agrega a su lista (RF-085).
 */
class ConvertToMember
{
    public static function handle(Sympathizer $persona): Member
    {
        $bautismo = $persona->stages()->where('stage', Sympathizer::BAPTISM_STAGE)->value('reached_at');

        $miembro = Member::create([
            'site_id' => $persona->site_id,
            'name' => $persona->name,
            'birth_date' => $persona->birth_date,
            'age_reported' => $persona->age_reported,
            'age_reported_at' => $persona->age_reported_at,
            'gender' => $persona->gender,
            'baptized' => $bautismo !== null,
            'baptized_at' => $bautismo,
            'joined_at' => now('America/Bogota')->toDateString(),
            'phone' => $persona->phone,
            'neighborhood' => $persona->neighborhood,
            'profile_pending' => true,
            'data_consent' => $persona->data_consent,
            'data_consent_at' => $persona->data_consent_at,
            'data_consent_means' => $persona->data_consent_means,
        ]);

        $persona->update(['converted_member_id' => $miembro->id, 'converted_at' => now('America/Bogota')->toDateString()]);

        return $miembro;
    }
}
