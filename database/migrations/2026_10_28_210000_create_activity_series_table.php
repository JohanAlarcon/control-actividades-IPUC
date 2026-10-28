<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Las actividades que se repiten (D-38, RF-088): la serie guarda cada cuánto y entre qué fechas, y
 * cada fecha es una actividad suelta que la recuerda en `series_id`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_series', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->foreignId('schedule_id')->constrained()->cascadeOnDelete();
            $table->enum('frequency', ['weekly', 'biweekly', 'monthly']);
            $table->date('starts_on');
            $table->date('ends_on');
            $table->timestamps();
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->foreignId('series_id')->nullable()->after('schedule_id')->constrained('activity_series')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('series_id');
        });

        Schema::dropIfExists('activity_series');
    }
};
