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
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Meal reminders
            $table->boolean('meal_reminder_enabled')->default(true);
            $table->time('breakfast_reminder_time')->default('08:00');
            $table->time('lunch_reminder_time')->default('12:00');
            $table->time('dinner_reminder_time')->default('18:00');

            // Exercise reminders
            $table->boolean('exercise_reminder_enabled')->default(true);
            $table->time('exercise_reminder_time')->default('07:00');

            // Weight reminders
            $table->boolean('weight_reminder_enabled')->default(true);
            $table->time('weight_reminder_time')->default('07:30');

            // Daily summary
            $table->boolean('daily_summary_enabled')->default(true);
            $table->time('daily_summary_time')->default('21:00');

            // Badge notifications
            $table->boolean('badge_notification_enabled')->default(true);

            // Email preferences
            $table->boolean('email_enabled')->default(true);

            // Push preferences
            $table->boolean('push_enabled')->default(true);

            $table->timestamps();

            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
