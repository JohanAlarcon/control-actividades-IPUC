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

    /**
     * Seis números, para las cuentas que se entregan de una vez en un cuadro (RF-123). Sin un cero
     * adelante, para que la hoja de cálculo no lo borre al abrir el cuadro.
     */
    public static function numerica(): string
    {
        return (string) random_int(100000, 999999);
    }
}
