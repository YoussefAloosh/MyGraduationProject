<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('emergency_groups')->cascadeOnDelete();
            $table->foreignId('rater_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('rated_id')->constrained('users')->cascadeOnDelete();
            $table->enum('score', ['positive', 'negative']);
            $table->timestamp('rated_at')->useCurrent();
            $table->boolean('is_edited')->default(false);
            $table->timestamp('edited_at')->nullable();
            $table->timestamps();

            $table->unique(['group_id', 'rater_id', 'rated_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
