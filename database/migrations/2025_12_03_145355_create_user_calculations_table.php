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
        Schema::create('user_calculations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('bmr', 8, 2)->comment('Base Metabolic Rate');
            $table->decimal('tdee', 8, 2)->comment('Total Daily Energy Expenditure');
            $table->decimal('target_calories', 8, 2)->comment('Daily target calories');
            $table->decimal('target_protein_g', 8, 2)->comment('Daily target protein in grams');
            $table->decimal('target_carbs_g', 8, 2)->comment('Daily target carbs in grams');
            $table->decimal('target_fat_g', 8, 2)->comment('Daily target fat in grams');
            $table->timestamp('calculated_at');
            $table->timestamps();

            $table->index('user_id');
            $table->index('calculated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_calculations');
    }
};
