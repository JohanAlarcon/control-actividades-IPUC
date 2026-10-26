<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Una actividad sin tareas la da por cumplida su responsable (R-04). Es un cambio de estado, asi
 * que queda con su nombre (principio III). La llave es restrict, como toda llave hacia una persona (D-22).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->foreignId('completed_by_user_id')->nullable()->after('completed_at')->constrained('users')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('completed_by_user_id');
        });
    }
};
