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
        Schema::table('diet_plans', function (Blueprint $table) {
            $table->foreignId('survey_submission_id')
                ->nullable()
                ->after('user_id')
                ->constrained('survey_submissions')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diet_plans', function (Blueprint $table) {
            $table->dropForeign(['survey_submission_id']);
            $table->dropColumn('survey_submission_id');
        });
    }
};
