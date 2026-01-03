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
        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->string('image_url')->nullable();
            $table->enum('goal_type', ['weight_loss', 'exercise_count', 'streak', 'meal_log', 'custom']);
            $table->decimal('goal_value', 10, 2);
            $table->string('goal_unit')->nullable(); // kg, 회, 일 등
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedInteger('participants_count')->default(0);
            $table->unsignedInteger('max_participants')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('badge_id')->nullable()->constrained('badges')->nullOnDelete();
            $table->timestamps();

            $table->index(['is_active', 'start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenges');
    }
};
