<?php

namespace App\Http\Controllers;

use App\Http\Requests\MoveInternalActivityRequest;
use App\Models\Activity;
use App\Support\Activities\ScheduleCards;
use Carbon\CarbonInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Mover o cancelar una sola fecha de un cronograma interno (CL-30): las demás fechas de su serie
 * siguen como estaban. Lo hace el presidente o el líder del comité dueño. Cancelar no borra: deja
 * quién y cuándo (principio III).
 */
class InternalActivityController extends Controller
{
    public function cancel(Request $request, Activity $activity): RedirectResponse
    {
        Gate::authorize('addActivity', $activity->schedule);

        if ($activity->cancelled_at === null) {
            $activity->update(['cancelled_at' => now(), 'cancelled_by_user_id' => $request->user()->id]);
        }

        return back()->with('notice', "Listo. «{$activity->title}» del ".$this->dia($activity->scheduled_at).' quedó cancelada. Las demás fechas siguen igual.');
    }

    public function move(MoveInternalActivityRequest $request, Activity $activity): RedirectResponse
    {
        $activity->update(['scheduled_at' => $request->scheduledAt(), 'ends_at' => $request->endsAt()]);

        return back()->with('notice', "Listo. «{$activity->title}» quedó para el ".$this->dia($activity->scheduled_at).'. Las demás fechas siguen igual.');
    }

    private function dia(CarbonInterface $momento): string
    {
        return $momento->copy()->setTimezone(ScheduleCards::ZONE)->locale('es')->isoFormat('dddd D [de] MMMM');
    }
}
