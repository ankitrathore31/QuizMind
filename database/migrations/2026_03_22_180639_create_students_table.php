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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('display_name')->nullable();
            $table->string('avatar')->nullable();
            $table->string('class')->nullable();          // e.g. "10th Grade", "Class 12"
            $table->unsignedTinyInteger('age')->nullable();
            $table->string('school_name')->nullable();
            $table->json('subjects_interest')->nullable(); // ["Math","Science","History"]
 
            // Gamification
            $table->unsignedInteger('level')->default(1);
            $table->unsignedInteger('xp')->default(0);
            $table->unsignedInteger('streak')->default(0);
            $table->date('streak_last_date')->nullable();
 
            // Stats
            $table->unsignedInteger('total_quizzes')->default(0);
            $table->unsignedInteger('total_correct')->default(0);
            $table->unsignedInteger('total_wrong')->default(0);
            $table->unsignedInteger('total_battles_won')->default(0);
            $table->unsignedInteger('total_battles_lost')->default(0);
 
            // Badges & Rank
            $table->json('badges')->nullable();
            $table->string('rank')->default('Unranked');
 
            $table->boolean('is_profile_complete')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
