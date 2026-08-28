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
        Schema::table('shows', function (Blueprint $table) {
            $table->unsignedInteger('number_of_seasons')->nullable();
            $table->unsignedInteger('number_of_episodes')->nullable();
            $table->unsignedInteger('episode_run_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shows', function (Blueprint $table) {
            $table->dropColumn(['number_of_seasons', 'number_of_episodes', 'episode_run_time']);
        });
    }
};
