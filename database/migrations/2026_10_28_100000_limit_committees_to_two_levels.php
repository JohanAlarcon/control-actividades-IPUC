<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * La estructura llega hasta dos niveles: comité y sub-comité (D-31). No cambia ninguna columna:
 * el modelo ya impide colgar un nodo de un sub-comité. Esta migración solo se niega a seguir si
 * la base trae un tercer nivel, para que nadie lo descubra después en una pantalla.
 */
return new class extends Migration
{
    public function up(): void
    {
        $tercerNivel = DB::table('committees')->where('depth', '>', 2)->count();

        if ($tercerNivel > 0) {
            throw new RuntimeException("Hay {$tercerNivel} grupos en un tercer nivel. La estructura llega hasta dos niveles: muévalos a un sub-comité o desactívelos antes de seguir.");
        }
    }

    public function down(): void {}
};
