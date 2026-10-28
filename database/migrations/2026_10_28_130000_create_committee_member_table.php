<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Un miembro pertenece a uno o varios comités (D-32, RF-014): la pertenencia pasa a su propia
 * tabla, con la función de la persona en ese comité. Quien ya estaba en un comité queda en él,
 * desde su fecha de ingreso. Una pertenencia no se borra: se cierra con su fecha.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('committee_member', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->restrictOnDelete();
            $table->foreignId('member_id')->constrained()->restrictOnDelete();
            $table->foreignId('committee_id')->constrained()->restrictOnDelete();
            $table->enum('role', ['board', 'teacher', 'assistant', 'member'])->default('member');
            $table->enum('board_position', ['president', 'secretary', 'treasurer', 'vocal'])->nullable();
            $table->date('since');
            $table->date('ended_at')->nullable();
            $table->timestamps();

            $table->index(['committee_id', 'ended_at']);
            $table->index(['member_id', 'ended_at']);
        });

        DB::table('members')->whereNotNull('committee_id')->orderBy('id')->get()->each(fn ($miembro) => DB::table('committee_member')->insert([
            'site_id' => $miembro->site_id,
            'member_id' => $miembro->id,
            'committee_id' => $miembro->committee_id,
            'role' => 'member',
            'since' => $miembro->joined_at ?? substr((string) $miembro->created_at, 0, 10),
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        Schema::table('members', function (Blueprint $table) {
            $table->dropConstrainedForeignId('committee_id');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->foreignId('committee_id')->nullable()->after('site_id')->constrained()->restrictOnDelete();
        });

        Schema::dropIfExists('committee_member');
    }
};
