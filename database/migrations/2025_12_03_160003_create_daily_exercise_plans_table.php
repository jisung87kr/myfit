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
        Schema::create('daily_exercise_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('diet_plan_id')->constrained()->onDelete('cascade');
            $table->integer('day_number')->comment('1-7');
            $table->date('date');
            $table->foreignId('exercise_id')->nullable()->constrained()->onDelete('set null');
            $table->string('exercise_name');
            $table->integer('duration_minutes');
            $table->decimal('estimated_calories_burned', 8, 2);
            $table->enum('intensity', ['낮음', '보통', '높음']);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('diet_plan_id');
            $table->index('date');
            $table->index('day_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_exercise_plans');
    }
};
