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
        Schema::create('battle_question_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('battle_rooms')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->integer('question_index');
            $table->integer('selected_option'); // -1 = timeout
            $table->boolean('is_correct')->default(false);
            $table->integer('time_ms')->default(0); // ms to answer
            $table->integer('points_earned')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('battle_question_answers');
    }
};
