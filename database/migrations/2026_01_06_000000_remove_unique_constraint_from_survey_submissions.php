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
        Schema::table('survey_submissions', function (Blueprint $table) {
            // 먼저 일반 인덱스 추가 (외래키가 사용할 수 있도록)
            $table->index('user_id', 'survey_submissions_user_id_index');
        });

        Schema::table('survey_submissions', function (Blueprint $table) {
            // 그 후 유니크 인덱스 삭제
            $table->dropUnique(['user_id', 'survey_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('survey_submissions', function (Blueprint $table) {
            $table->unique(['user_id', 'survey_id']);
        });

        Schema::table('survey_submissions', function (Blueprint $table) {
            $table->dropIndex('survey_submissions_user_id_index');
        });
    }
};
