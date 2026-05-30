<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_group_requests', function (Blueprint $table) {
            $table->id();
            $table->decimal('center_lat', 10, 7);
            $table->decimal('center_lng', 10, 7);
            $table->decimal('radius_km', 5, 2)->default(5.00);
            $table->unsignedInteger('nearby_users_count')->default(1);
            $table->enum('status', ['pending', 'submitted', 'completed'])->default('pending');
            $table->timestamp('submitted_to_manager_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_group_requests');
    }
};