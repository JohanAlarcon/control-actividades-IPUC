<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->boolean('done')->default(false);
            $table->string('assigned_to_name')->nullable();
            $table->timestamp('done_at')->nullable();
            $table->timestamps();

            $table->index(['activity_id', 'done']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_tasks');
    }
};
