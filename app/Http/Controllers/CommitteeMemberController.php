<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMembershipRequest;
use App\Models\Committee;
use App\Models\CommitteeMember;
use App\Models\Member;
use App\Support\Members\Memberships;
use App\Support\Scope\CurrentScope;
use Illuminate\Http\RedirectResponse;

/**
 * La lista de cada comité (RF-084, RF-085): el cargo agrega a una persona ya registrada, con su
 * función, o la quita. Nunca toca la ficha, que es del pastor. Quitar cierra la pertenencia con su
 * fecha: la persona sigue en el padrón y su historia se conserva.
 */
class CommitteeMemberController extends Controller
{
    public function store(StoreMembershipRequest $request, Committee $committee): RedirectResponse
    {
        $miembro = Member::forPicker()->whereKey($request->validated('member_id'))->firstOrFail();

        Memberships::join($miembro, $committee->id, $request->validated('role'), $request->validated('board_position'));

        return back()->with('notice', "Listo. {$miembro->name} quedó en la lista de {$committee->name}.");
    }

    public function destroy(CurrentScope $scope, Committee $committee, int $member): RedirectResponse
    {
        abort_unless($scope->isPastor() || $scope->activeRole() !== null, 403);

        $pertenencia = CommitteeMember::current()
            ->with('member:id,name')
            ->where('committee_id', $committee->id)
            ->where('member_id', $member)
            ->firstOrFail();

        Memberships::leave($pertenencia);

        return back()->with('notice', "Listo. {$pertenencia->member->name} salió de la lista de {$committee->name}. Su ficha sigue en el padrón.");
    }
}
