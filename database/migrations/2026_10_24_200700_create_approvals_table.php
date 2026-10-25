<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->foreignId('committee_id')->constrained()->cascadeOnDelete();
            $table->morphs('approvable');
            $table->enum('type', ['activity', 'expense', 'task', 'date_change', 'new_member']);
            $table->enum('level', ['president', 'pastor']);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('title');
            $table->text('detail')->nullable();
            $table->decimal('amount', 14, 2)->nullable();
            $table->foreignId('requested_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('requested_at');
            $table->foreignId('resolved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->text('reason')->nullable();
            $table->foreignId('transferred_from_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('transferred_at')->nullable();
            $table->timestamps();

            $table->index(['site_id', 'committee_id', 'status']);
            $table->index(['level', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approvals');
    }
};
