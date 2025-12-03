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
        Schema::create('survey_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->cascadeOnDelete();
            $table->integer('step')->default(1); // 설문 단계 (1-5)
            $table->text('question_text');
            $table->string('question_type'); // text, number, select, multi_select
            $table->json('options')->nullable(); // 선택형 질문의 옵션들
            $table->boolean('is_required')->default(true);
            $table->integer('order')->default(0); // 질문 순서
            $table->timestamps();

            $table->index(['survey_id', 'step']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_questions');
    }
};
