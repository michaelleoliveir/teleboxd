<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.'
     */
    public function up(): void
    {
        Schema::create('shows', function (Blueprint $table) {
            $table->id();
            $table->integer('tmdb_id')->unique();
            $table->string('name');
            $table->text('overview')->nullable();
            $table->string('poster_path')->nullable();
            $table->decimal('average_rating', 2, 1)->nullable();
            $table->integer('reviews_count')->default(0);
            $table->date('first_air_date')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shows');
    }
};
