<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_action_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('section', ['emergency', 'posts', 'courses', 'store', 'services']);
            $table->enum('action_type', [
                'grant_extra_messages',
                'restrict_user',
                'unrestrict_user',
                'remove_user',
                'ban_user',
                'promote_to_group_admin',
            ]);
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('target_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('group_id')->nullable()->constrained('emergency_groups')->nullOnDelete();
            $table->string('extra_value')->nullable();
            $table->timestamp('action_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_action_logs');
    }
};