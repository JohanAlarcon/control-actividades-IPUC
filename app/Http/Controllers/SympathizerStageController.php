<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSympathizerStageRequest;
use App\Models\Sympathizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

/** El camino de un simpatizante: cada etapa se anota en orden, con su fecha (RF-042). */
class SympathizerStageController extends Controller
{
    public function store(StoreSympathizerStageRequest $request, Sympathizer $sympathizer): RedirectResponse
    {
        DB::transaction(function () use ($request, $sympathizer) {
            $sympathizer->stages()->create([
                'site_id' => $sympathizer->site_id,
                'stage' => $request->integer('stage'),
                'reached_at' => $request->validated('reached_at'),
                'recorded_by_user_id' => $request->user()->id,
            ]);
            $sympathizer->update(['stage' => $request->integer('stage')]);
        });

        return back()->with('notice', 'Listo. La etapa quedó anotada con su fecha.');
    }
}
