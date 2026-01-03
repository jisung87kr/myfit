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
        Schema::create('exercise_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->foreignId('exercise_id')->nullable()->constrained('exercises')->onDelete('set null');
            $table->string('exercise_name');
            $table->integer('duration_minutes');
            $table->decimal('calories_burned', 8, 2);
            $table->enum('intensity', ['낮음', '보통', '높음', '매우 높음'])->nullable();
            $table->time('exercise_time')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('date');
            $table->index(['user_id', 'date']);
            $table->index('intensity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercise_logs');
    }
};
