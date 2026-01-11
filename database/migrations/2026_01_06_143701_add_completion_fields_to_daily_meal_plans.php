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
        Schema::table('daily_meal_plans', function (Blueprint $table) {
            $table->timestamp('meal_completed_at')->nullable()->after('tips');
            $table->timestamp('exercise_completed_at')->nullable()->after('meal_completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_meal_plans', function (Blueprint $table) {
            $table->dropColumn(['meal_completed_at', 'exercise_completed_at']);
        });
    }
};
