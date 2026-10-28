<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** El lema de cada año de la sede, con su cita bíblica, que registra el pastor (RF-082, HU-25). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_mottos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('year');
            $table->string('text', 160);
            $table->string('citation', 60)->nullable();
            $table->foreignId('recorded_by_user_id')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['site_id', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_mottos');
    }
};
