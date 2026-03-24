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
        Schema::create('quiz_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('quiz_id')->nullable()->constrained('quizzes')->nullOnDelete();
            $table->string('type')->default('solo');    // solo|1v1|group|team
            $table->unsignedInteger('score')->default(0);
            $table->unsignedInteger('total_q')->default(0);
            $table->unsignedTinyInteger('accuracy')->default(0);
            $table->unsignedInteger('xp_earned')->default(0);
            $table->string('subject')->nullable();
            $table->string('topic')->nullable();
            $table->string('difficulty')->nullable();
            $table->unsignedInteger('time_taken')->nullable(); // seconds
            $table->json('answer_log')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_results');
    }
};
