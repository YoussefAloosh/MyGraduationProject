<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('emergency_false_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rescue_participation_id')->constrained('rescue_participations')->cascadeOnDelete();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('group_id')->constrained('emergency_groups')->cascadeOnDelete();
            $table->timestamp('reported_at')->useCurrent();
            $table->timestamps();
            $table->unique(['rescue_participation_id', 'reporter_id'], 'uq_false_report_participation_reporter');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emergency_false_reports');
    }
};