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
        Schema::create('battel_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique(); // e.g. QM-X9K2
            $table->foreignId('host_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->enum('mode', ['1v1', 'group', 'team'])->default('1v1');
            $table->enum('status', ['waiting', 'in_progress', 'finished'])->default('waiting');
 
            // Team mode extras
            $table->string('team_a_name')->nullable();
            $table->string('team_b_name')->nullable();
            $table->integer('max_per_team')->default(20);
 
            // Config
            $table->integer('question_timer')->default(20); // seconds per question
            $table->integer('total_questions')->default(10);
 
            // Results (stored after finish)
            $table->json('final_scores')->nullable(); // [{user_id, name, score, team}]
            $table->string('winner_team')->nullable(); // for team mode
            $table->foreignId('winner_user_id')->nullable()->constrained('users')->nullOnDelete();
 
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('battel_rooms');
    }
};
