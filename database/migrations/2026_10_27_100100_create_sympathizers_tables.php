<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Los simpatizantes, su camino de cinco etapas y la bitacora de visitas (data-model §3). El
 * compromiso no se guarda: se calcula (D-10). Y el comite que los acompaña queda marcado, en vez
 * de reconocerse por su nombre, que puede cambiar (D-28).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('committees', function (Blueprint $table) {
            $table->boolean('follows_sympathizers')->default(false)->after('active');
        });

        Schema::create('sympathizers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->restrictOnDelete();
            $table->foreignId('committee_id')->constrained()->restrictOnDelete();
            $table->string('name', 120)->collation('utf8mb4_unicode_ci');
            $table->date('birth_date')->nullable();
            $table->unsignedTinyInteger('age_reported')->nullable();
            $table->date('age_reported_at')->nullable();
            $table->enum('gender', ['f', 'm']);
            $table->string('phone', 30)->nullable();
            $table->string('neighborhood', 80)->nullable();
            $table->enum('arrival_source', ['invitation', 'refam', 'campaign', 'family', 'neighbor', 'media']);
            $table->foreignId('invited_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('invited_by_name', 120)->nullable();
            $table->date('first_contact_at');
            $table->unsignedTinyInteger('stage')->default(1);
            $table->foreignId('converted_member_id')->nullable()->constrained('members')->restrictOnDelete();
            $table->date('converted_at')->nullable();
            $table->boolean('data_consent')->default(false);
            $table->date('data_consent_at')->nullable();
            $table->enum('data_consent_means', ['signature', 'verbal', 'message'])->nullable();
            $table->timestamps();
        });

        Schema::create('sympathizer_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->restrictOnDelete();
            $table->foreignId('sympathizer_id')->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('stage');
            $table->date('reached_at');
            $table->foreignId('recorded_by_user_id')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['sympathizer_id', 'stage']);
        });

        Schema::create('sympathizer_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->restrictOnDelete();
            $table->foreignId('sympathizer_id')->constrained()->restrictOnDelete();
            $table->date('visited_at');
            $table->enum('type', ['visit', 'call', 'study', 'service']);
            $table->foreignId('by_user_id')->constrained('users')->restrictOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sympathizer_visits');
        Schema::dropIfExists('sympathizer_stages');
        Schema::dropIfExists('sympathizers');

        Schema::table('committees', function (Blueprint $table) {
            $table->dropColumn('follows_sympathizers');
        });
    }
};
