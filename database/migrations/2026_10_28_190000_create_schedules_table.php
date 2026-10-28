<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Los cronogramas (D-35): el general de cada año, uno por sede y de ningún comité, y los internos
 * de cada comité o sub-comité, con su nombre y su periodo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->foreignId('committee_id')->nullable()->constrained()->cascadeOnDelete();
            $table->enum('kind', ['general', 'internal']);
            $table->unsignedSmallInteger('year');
            $table->string('name', 80)->nullable();
            $table->date('starts_on');
            $table->date('ends_on');
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['site_id', 'kind', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
