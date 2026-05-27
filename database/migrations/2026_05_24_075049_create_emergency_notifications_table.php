<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emergency_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emergency_id')->constrained('emergencies')->cascadeOnDelete();
            $table->foreignId('receiver_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_read')->default(false);
            $table->boolean('is_responded')->default(false);
            $table->enum('response', ['accepted', 'rejected', 'no_response'])->nullable();
            $table->unsignedInteger('notif_round')->default(1);
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emergency_notifications');
    }
};