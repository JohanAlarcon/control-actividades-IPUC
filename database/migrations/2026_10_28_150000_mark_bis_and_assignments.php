<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * BIS recibe a los simpatizantes y los asigna al comité que los acompaña (D-34, RF-097). La marca
 * del comité cambia de nombre —ya no «acompaña», sino «recibe»— y cada asignación queda escrita.
 * Los simpatizantes que ya existen quedan con su primera asignación, la del comité que tienen.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('committees', function (Blueprint $table) {
            $table->renameColumn('follows_sympathizers', 'receives_sympathizers');
        });

        Schema::create('sympathizer_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->restrictOnDelete();
            $table->foreignId('sympathizer_id')->constrained()->restrictOnDelete();
            $table->foreignId('committee_id')->constrained()->restrictOnDelete();
            $table->foreignId('assigned_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->dateTime('assigned_at');
            $table->timestamps();

            $table->index(['sympathizer_id', 'assigned_at']);
        });

        DB::table('sympathizers')->orderBy('id')->get()->each(fn ($persona) => DB::table('sympathizer_assignments')->insert([
            'site_id' => $persona->site_id,
            'sympathizer_id' => $persona->id,
            'committee_id' => $persona->committee_id,
            'assigned_by_user_id' => $persona->invited_by_user_id,
            'assigned_at' => $persona->created_at ?? now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]));
    }

    public function down(): void
    {
        Schema::dropIfExists('sympathizer_assignments');

        Schema::table('committees', function (Blueprint $table) {
            $table->renameColumn('receives_sympathizers', 'follows_sympathizers');
        });
    }
};
