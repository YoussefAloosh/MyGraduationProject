<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Guard: the column may already exist if the original migration
        // was regenerated (e.g. migrate:fresh) after softDeletes() was added to it.
        if (Schema::hasColumn('articles', 'deleted_at')) {
            return;
        }

        Schema::table('articles', function (Blueprint $table) {
            $table->softDeletes()->after('published_at');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
