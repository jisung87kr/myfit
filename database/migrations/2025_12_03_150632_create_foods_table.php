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
        Schema::create('foods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_en')->nullable();
            $table->enum('category', ['곡류', '단백질', '채소', '과일', '유제품', '견과류', '음료', '기타']);
            $table->decimal('serving_size', 8, 2)->comment('1인분 g');
            $table->decimal('calories', 8, 2)->comment('kcal');
            $table->decimal('protein_g', 8, 2)->comment('단백질 g');
            $table->decimal('carbs_g', 8, 2)->comment('탄수화물 g');
            $table->decimal('fat_g', 8, 2)->comment('지방 g');
            $table->decimal('fiber_g', 8, 2)->nullable()->comment('식이섬유 g');
            $table->decimal('sodium_mg', 8, 2)->nullable()->comment('나트륨 mg');
            $table->string('image_url')->nullable();
            $table->timestamps();

            $table->index('category');
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('foods');
    }
};
