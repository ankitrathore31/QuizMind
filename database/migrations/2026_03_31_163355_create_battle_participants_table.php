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
        Schema::create('battle_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('battle_rooms')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('team')->nullable(); // 'a' | 'b' | null (for 1v1/group)
            $table->enum('status', ['joined', 'ready', 'playing', 'finished', 'disqualified'])->default('joined');
 
            // Scores
            $table->integer('score')->default(0);
            $table->integer('correct')->default(0);
            $table->integer('wrong')->default(0);
            $table->integer('streak')->default(0);
            $table->integer('max_streak')->default(0);
            $table->integer('xp_earned')->default(0);
            $table->integer('time_taken')->default(0); // seconds
 
            // Anti-cheat
            $table->integer('tab_switches')->default(0);
            $table->integer('window_blurs')->default(0);
            $table->boolean('disqualified')->default(false);
            $table->string('disqualify_reason')->nullable();
 
            // Per-question answers
            $table->json('answer_log')->nullable(); // [{q_idx, selected, correct, time_ms}]
 
            $table->unique(['room_id', 'user_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('battle_participants');
    }
};
