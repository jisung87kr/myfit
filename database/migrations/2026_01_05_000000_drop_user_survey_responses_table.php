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
        // 먼저 diet_plans 테이블의 외래키와 컬럼 제거
        Schema::table('diet_plans', function (Blueprint $table) {
            $table->dropForeign(['survey_response_id']);
            $table->dropColumn('survey_response_id');
        });

        // 그 후 테이블 삭제
        Schema::dropIfExists('user_survey_responses');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 먼저 user_survey_responses 테이블 복원
        Schema::create('user_survey_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('survey_id')->constrained()->cascadeOnDelete();
            $table->foreignId('survey_question_id')->constrained()->cascadeOnDelete();
            $table->json('answer');
            $table->timestamp('answered_at')->useCurrent();
            $table->timestamps();

            $table->unique(['user_id', 'survey_question_id']);
            $table->index(['user_id', 'survey_id']);
        });

        // diet_plans 테이블에 survey_response_id 컬럼 복원
        Schema::table('diet_plans', function (Blueprint $table) {
            $table->foreignId('survey_response_id')
                ->nullable()
                ->after('user_id')
                ->constrained('user_survey_responses')
                ->onDelete('set null');
        });
    }
};
