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
        Schema::create('user_survey_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('survey_id')->constrained()->cascadeOnDelete();
            $table->foreignId('survey_question_id')->constrained()->cascadeOnDelete();
            $table->json('answer'); // 다양한 답변 형식 저장 (text, number, array)
            $table->timestamp('answered_at')->useCurrent();
            $table->timestamps();

            // 사용자당 각 질문에 하나의 답변만 허용
            $table->unique(['user_id', 'survey_question_id']);
            $table->index(['user_id', 'survey_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_survey_responses');
    }
};
