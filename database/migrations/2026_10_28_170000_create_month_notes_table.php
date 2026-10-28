<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La nota general de un mes del cronograma (RF-118): una campaña de todo el mes o un evento con la
 * fecha pendiente, que el formato de la sede escribe junto al nombre del mes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('month_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->string('text', 160);
            $table->enum('highlight', ['special_day', 'district', 'national'])->nullable();
            $table->foreignId('recorded_by_user_id')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['site_id', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('month_notes');
    }
};
