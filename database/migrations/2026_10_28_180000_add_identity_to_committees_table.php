<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La identidad de cada comité (RF-083, HU-26): su logo, guardado fuera de `public/` como los demás
 * archivos, su lema y sus objetivos, con quién los cambió por última vez y cuándo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('committees', function (Blueprint $table) {
            $table->string('logo_path')->nullable()->after('icon');
            $table->string('motto', 160)->nullable()->after('logo_path');
            $table->text('objectives')->nullable()->after('motto');
            $table->foreignId('identity_updated_by_user_id')->nullable()->after('objectives')->constrained('users')->restrictOnDelete();
            $table->dateTime('identity_updated_at')->nullable()->after('identity_updated_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('committees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('identity_updated_by_user_id');
            $table->dropColumn(['logo_path', 'motto', 'objectives', 'identity_updated_at']);
        });
    }
};
