<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rescue_participations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emergency_id')->constrained('emergencies')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_resolved_by_user')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->timestamp('accepted_at')->useCurrent();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->unique(['emergency_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rescue_participations');
    }
};