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
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('category', ['유산소', '근력', '스트레칭', '스포츠']);
            $table->enum('intensity', ['낮음', '보통', '높음']);
            $table->decimal('met_value', 4, 2)->comment('대사당량 MET');
            $table->decimal('calories_per_hour_per_kg', 6, 2)->comment('시간당 kg당 칼로리');
            $table->text('description')->nullable();
            $table->string('video_url')->nullable();
            $table->timestamps();

            $table->index('category');
            $table->index('intensity');
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercises');
    }
};
