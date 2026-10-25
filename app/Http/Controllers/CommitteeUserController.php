<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommitteeUserRequest;
use App\Models\Committee;
use App\Models\CommitteeUser;
use App\Models\User;
use App\Support\Users\AssignRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class CommitteeUserController extends Controller
{
    public function __construct(private readonly AssignRole $cargos) {}

    public function store(StoreCommitteeUserRequest $request, Committee $committee): RedirectResponse
    {
        $persona = User::query()
            ->where('site_id', $committee->site_id)
            ->findOrFail($request->validated('user_id'));

        $this->cargos->handle($persona, $committee, $request->validated('role'));

        return back()->with('notice', 'El cargo quedó asignado.');
    }

    public function destroy(CommitteeUser $role): RedirectResponse
    {
        Gate::authorize('is-pastor');

        $this->cargos->vacate($role);

        return back()->with('notice', 'El cargo quedó vacante. Sus pendientes esperan a quien lo reemplace.');
    }
}
