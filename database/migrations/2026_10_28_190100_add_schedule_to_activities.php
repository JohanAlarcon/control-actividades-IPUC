<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Toda actividad va en un cronograma (D-35). Hasta hoy todas eran del general, así que las que ya
 * existen quedan en el cronograma general de su año, que se crea aquí si todavía no estaba.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->unsignedBigInteger('schedule_id')->nullable()->after('annual_plan_id');
        });

        $generales = [];

        foreach (DB::table('activities')->orderBy('id')->get(['id', 'site_id', 'scheduled_at']) as $actividad) {
            $anio = Carbon::parse($actividad->scheduled_at)->year;
            $generales[$actividad->site_id][$anio] ??= DB::table('schedules')->insertGetId([
                'site_id' => $actividad->site_id,
                'kind' => 'general',
                'year' => $anio,
                'starts_on' => "{$anio}-01-01",
                'ends_on' => "{$anio}-12-31",
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('activities')->where('id', $actividad->id)->update(['schedule_id' => $generales[$actividad->site_id][$anio]]);
        }

        Schema::table('activities', function (Blueprint $table) {
            $table->unsignedBigInteger('schedule_id')->nullable(false)->change();
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->foreign('schedule_id')->references('id')->on('schedules')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('schedule_id');
        });
    }
};
