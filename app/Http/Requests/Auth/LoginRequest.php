<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Se entra con el usuario que asigno el pastor, no con correo (HU-01).
 * Cinco intentos fallidos bloquean quince minutos (RF-004).
 */
class LoginRequest extends FormRequest
{
    private const INTENTOS = 5;

    private const BLOQUEO_EN_SEGUNDOS = 900;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'Escriba su usuario.',
            'password.required' => 'Escriba su clave.',
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credenciales = [
            'username' => $this->string('username')->trim()->toString(),
            'password' => $this->string('password')->toString(),
            'active' => true,
        ];

        if (! Auth::attempt($credenciales, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey(), self::BLOQUEO_EN_SEGUNDOS);

            throw ValidationException::withMessages([
                'username' => 'El usuario o la clave no coinciden. Revise e intente de nuevo.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        $this->user()->forceFill(['last_login_at' => now()])->saveQuietly();
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), self::INTENTOS)) {
            return;
        }

        event(new Lockout($this));

        $minutos = ceil(RateLimiter::availableIn($this->throttleKey()) / 60);

        throw ValidationException::withMessages([
            'username' => "Por seguridad, el acceso quedó bloqueado {$minutos} minutos. "
                .'Si olvidó su clave, pídale al pastor que se la restablezca.',
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('username')).'|'.$this->ip());
    }
}
