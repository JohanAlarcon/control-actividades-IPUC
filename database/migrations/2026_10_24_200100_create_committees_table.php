<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('committees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('committees')->cascadeOnDelete();
            $table->unsignedTinyInteger('depth');
            $table->string('name');
            $table->string('slug');
            $table->text('mission')->nullable();
            $table->string('color')->nullable();
            $table->string('icon')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['site_id', 'parent_id', 'name']);
            $table->unique(['site_id', 'slug']);
            $table->index(['site_id', 'depth']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('committees');
    }
};
