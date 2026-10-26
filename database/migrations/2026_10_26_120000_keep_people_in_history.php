<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Una cuenta nunca se borra (RN-09, principio III). Las llaves hacia las personas borraban en
 * cascada sus cargos, sus actividades y sus solicitudes, o dejaban en blanco quien aprobo algo.
 * Ahora la base de datos se niega a borrar a quien tenga historia.
 */
return new class extends Migration
{
    /** Cada llave hacia una persona, con lo que hacia antes al borrarla. */
    private const LLAVES = [
        'committee_user' => ['user_id' => 'cascade'],
        'annual_plans' => ['approved_by_user_id' => 'set null'],
        'activities' => ['proposed_by_user_id' => 'cascade', 'approved_by_user_id' => 'set null'],
        'approvals' => ['requested_by_user_id' => 'cascade', 'resolved_by_user_id' => 'set null', 'transferred_from_user_id' => 'set null'],
    ];

    public function up(): void
    {
        $this->cambiarLlaves(fn () => 'restrict');
    }

    public function down(): void
    {
        $this->cambiarLlaves(fn (string $antes) => $antes);
    }

    private function cambiarLlaves(Closure $regla): void
    {
        foreach (self::LLAVES as $tabla => $columnas) {
            Schema::table($tabla, function (Blueprint $table) use ($columnas, $regla) {
                foreach ($columnas as $columna => $antes) {
                    $table->dropForeign([$columna]);
                    $table->foreign($columna)->references('id')->on('users')->onDelete($regla($antes));
                }
            });
        }
    }
};
