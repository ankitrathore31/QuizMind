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
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title')->nullable();
            $table->string('subject')->nullable();
            $table->string('topic')->nullable();
            $table->string('class')->nullable();
            $table->string('difficulty')->default('intermediate');
            $table->string('source')->default('topic'); // topic|pdf|image|manual|standard
            $table->json('questions');                  // [{question, options[], answer, explanation, topic}]
            $table->boolean('is_public')->default(false);
            $table->unsignedInteger('play_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
