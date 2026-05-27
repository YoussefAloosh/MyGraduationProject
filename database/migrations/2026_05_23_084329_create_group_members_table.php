<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('group_id')->constrained('emergency_groups')->cascadeOnDelete();
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
            $table->enum('membership_status', ['active', 'left', 'removed', 'banned_temporarily', 'expired'])->default('active');
            $table->timestamp('last_activity_at')->nullable();
            $table->enum('membership_type', ['permanent', 'temporary'])->default('permanent');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('extra_messages_allowed')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'group_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_members');
    }
};