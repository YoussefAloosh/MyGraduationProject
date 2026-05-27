<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emergencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('target_group_id')->constrained('emergency_groups')->cascadeOnDelete();
            $table->string('case_type');
            $table->text('custom_text')->nullable();
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('critical');
            $table->unsignedInteger('required_rescuers')->default(0);
            $table->decimal('location_lat', 10, 7);
            $table->decimal('location_lng', 10, 7);
            $table->enum('status', ['new', 'in_progress', 'completed_quota', 'resolved', 'false'])->default('new');
            $table->boolean('is_false')->default(false);
            $table->unsignedTinyInteger('retry_count')->default(0);
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emergencies');
    }
};