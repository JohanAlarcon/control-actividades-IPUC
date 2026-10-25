<?php

namespace App\Support\Users;

use App\Models\User;
use Illuminate\Support\Str;

/**
 * El usuario lo genera el sistema a partir del nombre (HU-02, escenario 1):
 * "Hermano Rigoberto Paez" queda como rigoberto.paez. Sin tildes ni mayusculas,
 * para que se pueda escribir en cualquier celular sin buscar teclas.
 */
class UsernameFromName
{
    private const TRATAMIENTOS = ['hermano', 'hermana', 'pastor', 'pastora'];

    public static function generar(string $nombre): string
    {
        $palabras = collect(preg_split('/\s+/', Str::lower(Str::ascii(trim($nombre)))))
            ->map(fn (string $palabra) => preg_replace('/[^a-z0-9]/', '', $palabra))
            ->filter()
            ->reject(fn (string $palabra) => in_array($palabra, self::TRATAMIENTOS, true))
            ->values();

        $base = $palabras->count() > 1
            ? $palabras->first().'.'.$palabras->last()
            : ($palabras->first() ?? 'hermano');

        $usuario = $base;
        $numero = 2;

        while (User::where('username', $usuario)->exists()) {
            $usuario = $base.$numero++;
        }

        return $usuario;
    }
}
