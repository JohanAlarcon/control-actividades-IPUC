<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Las clases de actividad dejan de ser una lista fija en la columna `type` y pasan a una tabla que
 * administra el pastor (D-25). Cada sede arranca con las ocho de antes, y cada actividad que ya
 * existe queda apuntando a la suya.
 */
return new class extends Migration
{
    /** Las ocho clases de antes: el valor que tenia la columna y el nombre que se usa en la iglesia. */
    private const CLASES = [
        ['service', 'Culto'],
        ['rehearsal', 'Ensayo'],
        ['meeting', 'Reunión'],
        ['event', 'Evento'],
        ['outing', 'Salida'],
        ['visit', 'Visita'],
        ['campaign', 'Campaña'],
        ['convention', 'Convención'],
    ];

    public function up(): void
    {
        Schema::create('activity_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->restrictOnDelete();
            $table->string('name', 60);
            $table->boolean('active')->default(true);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['site_id', 'name']);
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->foreignId('activity_type_id')->nullable()->after('location')->constrained()->restrictOnDelete();
        });

        foreach (DB::table('sites')->pluck('id') as $sede) {
            foreach (self::CLASES as $posicion => [$valor, $nombre]) {
                $clase = DB::table('activity_types')->insertGetId([
                    'site_id' => $sede,
                    'name' => $nombre,
                    'active' => true,
                    'position' => $posicion,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('activities')->where('site_id', $sede)->where('type', $valor)->update(['activity_type_id' => $clase]);
            }
        }

        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->unsignedBigInteger('activity_type_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->enum('type', array_column(self::CLASES, 0))->nullable()->after('location');
        });

        foreach (self::CLASES as [$valor, $nombre]) {
            $clases = DB::table('activity_types')->where('name', $nombre)->pluck('id');
            DB::table('activities')->whereIn('activity_type_id', $clases)->update(['type' => $valor]);
        }

        Schema::table('activities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('activity_type_id');
        });

        Schema::dropIfExists('activity_types');
    }
};
