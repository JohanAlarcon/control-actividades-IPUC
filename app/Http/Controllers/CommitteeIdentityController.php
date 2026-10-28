<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCommitteeIdentityRequest;
use App\Models\Committee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * La identidad de cada comité (RF-083, HU-26): su logo, su lema y sus objetivos. La cambia el
 * responsable del nodo; la ven quienes tienen el comité en su ámbito. Fuera de él solo viaja el
 * nombre. El logo no se sirve desde `public/`: pasa por esta ruta, con el filtro del cargo.
 */
class CommitteeIdentityController extends Controller
{
    public function edit(Committee $committee): Response
    {
        Gate::authorize('updateIdentity', $committee);

        return Inertia::render('Committees/Identity', [
            'committee' => [
                'id' => $committee->id,
                'name' => $committee->name,
                'logoUrl' => $committee->logo_path ? "/committees/{$committee->id}/logo?v={$committee->identity_updated_at?->timestamp}" : null,
                'motto' => $committee->motto,
                'objectives' => $committee->objectives,
            ],
        ]);
    }

    public function update(UpdateCommitteeIdentityRequest $request, Committee $committee): RedirectResponse
    {
        $logo = $committee->logo_path;

        if ($request->hasFile('logo') || $request->boolean('remove_logo')) {
            if ($logo) {
                Storage::disk('local')->delete($logo);
            }
            $logo = $request->hasFile('logo') ? $request->file('logo')->store("committees/{$committee->id}", 'local') : null;
        }

        $committee->update([
            'logo_path' => $logo,
            'motto' => $request->validated('motto'),
            'objectives' => $request->validated('objectives'),
            'identity_updated_by_user_id' => $request->user()->id,
            'identity_updated_at' => now(),
        ]);

        return redirect("/committees/{$committee->id}")->with('notice', "Listo. La identidad de {$committee->name} quedó guardada.");
    }

    /** El logo, solo para quien tiene el comité en su ámbito: la ruta pasa por el filtro (HU-26 esc. 4). */
    public function logo(Committee $committee): BinaryFileResponse
    {
        abort_unless($committee->logo_path && Storage::disk('local')->exists($committee->logo_path), 404);

        return response()->file(Storage::disk('local')->path($committee->logo_path), [
            'Cache-Control' => 'private, max-age=86400',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
