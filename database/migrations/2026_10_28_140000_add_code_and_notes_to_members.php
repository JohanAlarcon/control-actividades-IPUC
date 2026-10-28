<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Lo que pide la ficha del levantamiento (RF-031, S-12): un código que pone el sistema en orden de
 * registro y no cambia, las observaciones, y el estado civil «divorciado».
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('code', 12)->nullable()->after('site_id');
            $table->text('notes')->nullable()->after('neighborhood');
            $table->enum('marital_status', ['single', 'married', 'common_law', 'separated', 'divorced', 'widowed'])->default('single')->change();
        });

        DB::table('members')->orderBy('id')->get(['id'])->each(fn ($miembro) => DB::table('members')
            ->where('id', $miembro->id)
            ->update(['code' => sprintf('M-%04d', $miembro->id)]));

        Schema::table('members', function (Blueprint $table) {
            $table->unique(['site_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropUnique(['site_id', 'code']);
            $table->dropColumn(['code', 'notes']);
        });
    }
};
