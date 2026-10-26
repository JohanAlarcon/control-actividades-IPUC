<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreActivityRequest;
use App\Models\Activity;
use App\Models\ActivityType;
use App\Models\Approval;
use App\Support\Approvals\ApprovalFlow;
use App\Support\Scope\CurrentScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ActivityController extends Controller
{
    public function __construct(
        private readonly CurrentScope $scope,
        private readonly ApprovalFlow $flujo,
    ) {}

    /** El formulario dice desde antes a quien le va a llegar la propuesta, y ofrece las clases vigentes. */
    public function create(): Response
    {
        Gate::authorize('create', Activity::class);

        return Inertia::render('Activities/Form', [
            'recipient' => $this->flujo->recipientFor($this->scope->activeRole())?->name,
            'types' => ActivityType::where('active', true)->orderBy('position')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * La actividad queda propuesta y su solicitud, en manos de quien le toca (RF-016, RF-017). Las
     * dos cosas pasan juntas o ninguna: no queda una propuesta sin solicitud.
     */
    public function store(StoreActivityRequest $request): RedirectResponse
    {
        $cargo = $this->scope->activeRole();

        DB::transaction(function () use ($request, $cargo) {
            $actividad = Activity::create([
                'site_id' => $cargo->site_id,
                'committee_id' => $cargo->committee_id,
                'title' => $request->validated('title'),
                'description' => $request->validated('description'),
                'scheduled_at' => $request->scheduledAt(),
                'location' => $request->validated('location'),
                'activity_type_id' => $request->validated('activity_type_id'),
                'status' => Activity::PENDING,
                'budget_amount' => $request->validated('budget'),
                'proposed_by_user_id' => $cargo->user_id,
                'proposed_at' => now(),
            ]);

            $monto = $actividad->budget_amount === null ? null : (float) $actividad->budget_amount;
            $this->flujo->request($cargo, $actividad, Approval::TYPE_ACTIVITY, $actividad->title, $actividad->description, $monto);
        });

        $quien = $this->flujo->recipientFor($cargo)?->name;

        return redirect('/approvals')->with('notice', $quien
            ? "Listo. Su propuesta le llegó a {$quien}."
            : 'Listo. Su propuesta quedó registrada. Su comité todavía no tiene quién la revise: le llegará a la persona que el pastor nombre.');
    }
}
