<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Una actividad nueva que aprueba el presidente no cierra: sube al pastor (HU-06, escenario 2).
 * Esa aprobacion intermedia tambien es una decision, y tiene que quedar con nombre y fecha
 * (principio III, RF-021): la bandeja del pastor muestra por quien ya paso.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('approvals', function (Blueprint $table) {
            $table->foreignId('escalated_by_user_id')->nullable()->after('level')->constrained('users')->restrictOnDelete();
            $table->timestamp('escalated_at')->nullable()->after('escalated_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('approvals', function (Blueprint $table) {
            $table->dropConstrainedForeignId('escalated_by_user_id');
            $table->dropColumn('escalated_at');
        });
    }
};
