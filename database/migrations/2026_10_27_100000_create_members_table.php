<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La ficha unica de cada miembro (data-model §2). La edad no se guarda suelta: se calcula de la
 * fecha de nacimiento o de la edad anotada con su fecha (RF-031). El nombre y el barrio comparan
 * sin distinguir mayusculas ni tildes (RF-034), lo configure como lo configure el servidor.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->restrictOnDelete();
            $table->foreignId('committee_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('name', 120)->collation('utf8mb4_unicode_ci');
            $table->date('birth_date')->nullable();
            $table->unsignedTinyInteger('age_reported')->nullable();
            $table->date('age_reported_at')->nullable();
            $table->enum('gender', ['f', 'm']);
            $table->enum('marital_status', ['single', 'married', 'widowed', 'separated', 'common_law'])->default('single');
            $table->boolean('baptized')->default(false);
            $table->date('baptized_at')->nullable();
            $table->date('joined_at')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('neighborhood', 80)->nullable()->collation('utf8mb4_unicode_ci');
            $table->boolean('adult_confirmed')->default(false);
            $table->date('adult_confirmed_at')->nullable();
            $table->boolean('profile_pending')->default(false);
            $table->boolean('data_consent')->default(false);
            $table->date('data_consent_at')->nullable();
            $table->enum('data_consent_means', ['signature', 'verbal', 'message'])->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['site_id', 'committee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
