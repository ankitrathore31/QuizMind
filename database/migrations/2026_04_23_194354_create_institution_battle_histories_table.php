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
        Schema::create('institution_battle_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('battle_id')->constrained('institution_battles')->cascadeOnDelete();
            $table->integer('total_participants')->default(0);
            $table->integer('total_correct')->default(0);
            $table->integer('total_wrong')->default(0);
            $table->integer('total_score')->default(0);
            $table->integer('average_accuracy')->default(0); // percentage
            $table->integer('average_time')->default(0); // seconds
            $table->integer('rank')->nullable(); // 1st, 2nd, 3rd place
            $table->timestamps();

            $table->unique(['institution_id', 'battle_id']);
            $table->index('rank');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institution_battle_histories');
    }
};