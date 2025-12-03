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
        Schema::create('diet_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('survey_response_id')->nullable()->constrained('user_survey_responses')->onDelete('set null');
            $table->enum('status', ['generating', 'active', 'completed', 'archived'])->default('generating');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('target_calories_per_day', 8, 2);
            $table->text('ai_summary')->nullable()->comment('AI가 생성한 플랜 요약');
            $table->text('generation_prompt')->nullable()->comment('사용한 프롬프트');
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
            $table->index('start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diet_plans');
    }
};
