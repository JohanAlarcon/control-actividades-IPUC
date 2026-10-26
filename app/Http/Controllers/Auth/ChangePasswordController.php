<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class ChangePasswordController extends Controller
{
    public function create(Request $request): Response
    {
        return Inertia::render('auth/ChangePassword', [
            'forced' => (bool) $request->user()->must_change_password,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'password.required' => 'Escriba su clave nueva.',
            'password.confirmed' => 'Las dos claves no coinciden. Escríbalas de nuevo.',
            'password.min' => 'La clave debe tener al menos 8 letras o números.',
        ]);

        $request->user()->forceFill([
            'password' => Hash::make($datos['password']),
            'must_change_password' => false,
        ])->save();

        return redirect()->route('dashboard')
            ->with('notice', 'Su clave quedó cambiada. Use la nueva la próxima vez que entre.');
    }
}
