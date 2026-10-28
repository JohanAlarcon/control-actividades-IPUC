<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Toda actividad tiene hora de inicio y de fin (RF-028, D-37): sin la de fin no se sabe si dos
 * actividades del cronograma general se cruzan. Las que ya existen quedan de dos horas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dateTime('ends_at')->nullable()->after('scheduled_at');
        });

        DB::table('activities')->update(['ends_at' => DB::raw('DATE_ADD(scheduled_at, INTERVAL 2 HOUR)')]);

        Schema::table('activities', function (Blueprint $table) {
            $table->dateTime('ends_at')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn('ends_at');
        });
    }
};
