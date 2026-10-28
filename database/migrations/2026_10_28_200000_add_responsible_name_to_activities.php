<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Quién dirige una actividad interna —la maestra de la clase, el director del ensayo—, escrito a
 * mano como en las tareas (HU-27 escenario 2). Es del comité: no viaja en el cronograma compartido.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->string('responsible_name', 80)->nullable()->after('location');
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn('responsible_name');
        });
    }
};
