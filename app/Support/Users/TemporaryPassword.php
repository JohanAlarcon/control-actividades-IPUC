<?php

namespace App\Support\Users;

/**
 * El pastor le dicta esta clave por telefono a alguien que puede ser mayor, asi
 * que tiene que poder leerse en voz alta sin deletrear: una palabra conocida y
 * cuatro numeros. Dura poco, porque al entrar se exige cambiarla (RF-002).
 */
class TemporaryPassword
{
    private const PALABRAS = [
        'monte', 'cedro', 'siembra', 'camino', 'puerta',
        'estrella', 'fuente', 'palma', 'aurora', 'cosecha',
    ];

    public static function generar(): string
    {
        $palabra = self::PALABRAS[random_int(0, count(self::PALABRAS) - 1)];

        return $palabra.random_int(1000, 9999);
    }
}
