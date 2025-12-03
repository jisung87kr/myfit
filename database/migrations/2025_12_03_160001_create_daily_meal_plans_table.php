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
        Schema::create('daily_meal_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('diet_plan_id')->constrained()->onDelete('cascade');
            $table->integer('day_number')->comment('1-7');
            $table->date('date');
            $table->decimal('total_calories', 8, 2);
            $table->decimal('total_protein_g', 8, 2);
            $table->decimal('total_carbs_g', 8, 2);
            $table->decimal('total_fat_g', 8, 2);
            $table->text('tips')->nullable();
            $table->timestamps();

            $table->index('diet_plan_id');
            $table->index('date');
            $table->unique(['diet_plan_id', 'day_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_meal_plans');
    }
};
