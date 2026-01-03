<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Notification Commands
|--------------------------------------------------------------------------
|
| These commands send notifications to users based on their settings.
| Run the scheduler every minute: * * * * * php artisan schedule:run
|
*/

// Meal reminders - run every minute to check for matching times
Schedule::command('notifications:meal-reminders breakfast')->everyMinute();
Schedule::command('notifications:meal-reminders lunch')->everyMinute();
Schedule::command('notifications:meal-reminders dinner')->everyMinute();

// Exercise reminder
Schedule::command('notifications:exercise-reminders')->everyMinute();

// Weight reminder
Schedule::command('notifications:weight-reminders')->everyMinute();

// Daily summary
Schedule::command('notifications:daily-summary')->everyMinute();
