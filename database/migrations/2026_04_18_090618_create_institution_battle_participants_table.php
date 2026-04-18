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
        Schema::create('institution_battle_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('battle_id')->constrained('institution_battles')->cascadeOnDelete();
            $table->foreignId('institution_id')->nullable()->constrained('institutions')->nullOnDelete();
            $table->string('name', 150);
            $table->string('student_code', 20)->unique();
            $table->boolean('is_host')->default(false);
            $table->unsignedBigInteger('total_score')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institution_battle_participants');
    }
};
