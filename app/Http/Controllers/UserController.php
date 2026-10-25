<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Committee;
use App\Models\User;
use App\Support\Scope\CurrentScope;
use App\Support\Users\AssignRole;
use App\Support\Users\TemporaryPassword;
use App\Support\Users\UsernameFromName;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Quien administra el acceso de toda la congregacion es el pastor (RF-003).
 */
class UserController extends Controller
{
    public function __construct(
        private readonly CurrentScope $scope,
        private readonly AssignRole $cargos,
    ) {}

    public function index(): Response
    {
        Gate::authorize('viewAny', User::class);

        return Inertia::render('Users/Index', [
            'accounts' => $this->cuentasDeLaSede(),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', User::class);

        return Inertia::render('Users/Form', [
            'account' => null,
            'roles' => [],
            'committees' => $this->comitesParaEscoger(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $clave = TemporaryPassword::generar();

        $persona = User::create([
            'site_id' => $this->scope->siteId(),
            'name' => $request->validated('name'),
            'username' => $request->validated('username') ?: UsernameFromName::generar($request->validated('name')),
            'password' => $clave,
            'must_change_password' => true,
            'active' => true,
        ]);

        if ($request->validated('committee_id')) {
            $comite = Committee::findOrFail($request->validated('committee_id'));
            $this->cargos->handle($persona, $comite, $request->validated('role'));
        }

        return $this->conLaClave($persona, $clave, 'La cuenta quedó creada.');
    }

    public function edit(User $user): Response
    {
        Gate::authorize('update', $user);

        return Inertia::render('Users/Form', [
            'account' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
            ],
            'roles' => $user->currentRoles()->get(['id', 'role', 'committee_id'])
                ->map(fn ($cargo) => [
                    'id' => $cargo->id,
                    'role' => $cargo->role,
                    'committeeId' => $cargo->committee_id,
                ])->all(),
            'committees' => $this->comitesParaEscoger(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $user->update($request->validated());

        return redirect()->route('users.index')->with('notice', 'Los datos quedaron guardados.');
    }

    public function resetPassword(User $user): RedirectResponse
    {
        Gate::authorize('resetPassword', $user);

        $clave = TemporaryPassword::generar();
        $user->update(['password' => $clave, 'must_change_password' => true]);

        return $this->conLaClave($user, $clave, 'La clave quedó restablecida.');
    }

    public function revoke(User $user): RedirectResponse
    {
        Gate::authorize('revoke', $user);

        $user->update(['active' => false]);

        return redirect()->route('users.index')
            ->with('notice', $user->name.' ya no puede entrar. Su nombre sigue en la historia.');
    }

    public function restore(User $user): RedirectResponse
    {
        Gate::authorize('update', $user);

        $clave = TemporaryPassword::generar();
        $user->update(['active' => true, 'password' => $clave, 'must_change_password' => true]);

        return $this->conLaClave($user, $clave, 'La cuenta quedó habilitada otra vez.');
    }

    /**
     * La clave temporal se muestra una sola vez, para que el pastor la copie.
     * No se guarda en ningun registro ni se vuelve a mostrar.
     */
    private function conLaClave(User $persona, string $clave, string $aviso): RedirectResponse
    {
        return redirect()->route('users.index')
            ->with('notice', $aviso)
            ->with('temporaryPassword', [
                'name' => $persona->name,
                'username' => $persona->username,
                'password' => $clave,
            ]);
    }

    private function cuentasDeLaSede(): array
    {
        return User::query()
            ->where('site_id', $this->scope->siteId())
            ->with('currentRoles.committee')
            ->orderBy('name')
            ->get()
            ->map(fn (User $cuenta) => [
                'id' => $cuenta->id,
                'name' => $cuenta->name,
                'username' => $cuenta->username,
                'isPastor' => $cuenta->is_pastor,
                'active' => $cuenta->active,
                'mustChangePassword' => $cuenta->must_change_password,
                'roles' => $cuenta->currentRoles->map(fn ($cargo) => [
                    'id' => $cargo->id,
                    'role' => $cargo->role,
                    'committeeName' => $cargo->committee?->name,
                ])->all(),
            ])->all();
    }

    private function comitesParaEscoger(): array
    {
        $todos = Committee::query()
            ->orderBy('position')
            ->orderBy('name')
            ->get(['id', 'parent_id', 'name', 'depth', 'active']);

        return $this->enOrdenDeArbol($todos, null);
    }

    /** Cada comite seguido de sus hijos, para que el selector se lea como la estructura. */
    private function enOrdenDeArbol(Collection $todos, ?int $padre): array
    {
        $lista = [];

        foreach ($todos->where('parent_id', $padre) as $comite) {
            $lista[] = [
                'id' => $comite->id,
                'name' => $comite->name,
                'depth' => $comite->depth,
                'active' => $comite->active,
            ];
            array_push($lista, ...$this->enOrdenDeArbol($todos, $comite->id));
        }

        return $lista;
    }
}
