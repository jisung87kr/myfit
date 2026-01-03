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
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description');
            $table->string('icon')->nullable();
            $table->string('category'); // streak, meal, exercise, weight, milestone
            $table->string('type'); // bronze, silver, gold, platinum
            $table->integer('requirement_value');
            $table->string('requirement_type'); // days, count, calories, kg
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('category');
            $table->index('type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};
