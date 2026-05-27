<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('emergency_groups')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('emergency_id')->nullable()->constrained('emergencies')->nullOnDelete();
            $table->string('content', 500);
            $table->timestamp('sent_at')->useCurrent();
            $table->boolean('is_emergency_mode')->default(false);
            $table->boolean('is_reported_spam')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_chat_messages');
    }
};