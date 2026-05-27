<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_group_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pending_group_id')->constrained('pending_group_requests')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('added_at')->useCurrent();
            $table->timestamps();

            $table->unique(['pending_group_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_group_users');
    }
};