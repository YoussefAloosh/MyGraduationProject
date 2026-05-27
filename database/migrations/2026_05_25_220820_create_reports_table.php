<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reported_id')->constrained('users')->cascadeOnDelete();
            $table->enum('report_type', [
                'false_emergency',
                'spam_message',
                'fake_rescue',
                'group_admin_misconduct',
            ]);
            $table->foreignId('emergency_id')->nullable()->constrained('emergencies')->nullOnDelete();
            $table->foreignId('message_id')->nullable()->constrained('group_chat_messages')->nullOnDelete();
            $table->foreignId('rescue_participation_id')->nullable()->constrained('rescue_participations')->nullOnDelete();
            $table->text('details')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('reported_at')->useCurrent();
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};