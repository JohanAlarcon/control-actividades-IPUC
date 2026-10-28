<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lo que trae el cronograma de la sede en cada casilla (RF-116, D-47): el tema o lema del culto,
 * en azul, y una marca de color —rojo para los días especiales, naranja para lo del distrito o lo
 * nacional—. Las dos son opcionales.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->string('theme', 120)->nullable()->after('title');
            $table->enum('highlight', ['special_day', 'district', 'national'])->nullable()->after('theme');
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn(['theme', 'highlight']);
        });
    }
};
