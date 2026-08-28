<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('watched_seasons', function (Blueprint $table) {
            $table->unsignedSmallInteger('last_watched_episode')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('watched_seasons', function (Blueprint $table) {
            $table->dropColumn('last_watched_episode');
        });
    }
};
