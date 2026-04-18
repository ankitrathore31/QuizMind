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
        Schema::create('institution_battles', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->foreignId('host_institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->enum('status', ['waiting', 'in_progress', 'finished'])->default('waiting');
            $table->unsignedTinyInteger('institution_count')->default(2);
            $table->unsignedSmallInteger('student_limit')->default(50);
            $table->unsignedSmallInteger('question_timer')->default(20);
            $table->unsignedSmallInteger('total_questions')->default(0);
            $table->unsignedSmallInteger('current_question')->default(0);
            $table->boolean('anti_cheat')->default(true);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->json('final_scores')->nullable();
            $table->foreignId('winner_inst_id')->nullable()->constrained('institution_battle_participants')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institution_battles');
    }
};
