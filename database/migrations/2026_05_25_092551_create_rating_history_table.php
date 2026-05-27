<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rating_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rating_id')->constrained('ratings')->cascadeOnDelete();
            $table->enum('old_score', ['positive', 'negative']);
            $table->enum('new_score', ['positive', 'negative']);
            $table->timestamp('changed_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rating_history');
    }
};