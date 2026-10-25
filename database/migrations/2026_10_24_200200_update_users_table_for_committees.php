<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('site_id')->after('id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('username')->after('name')->nullable();
            $table->boolean('must_change_password')->after('password')->default(true);
            $table->boolean('is_pastor')->after('must_change_password')->default(false);
            $table->boolean('active')->after('is_pastor')->default(true);
            $table->timestamp('last_login_at')->after('active')->nullable();

            $table->unique(['site_id', 'username']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['site_id', 'username']);
            $table->dropConstrainedForeignId('site_id');
            $table->dropColumn(['username', 'must_change_password', 'is_pastor', 'active', 'last_login_at']);
        });
    }
};
