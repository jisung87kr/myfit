<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DailySummaryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public array $summary
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        $settings = $notifiable->notificationSettings;
        if ($settings?->email_enabled) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $caloriesConsumed = $this->summary['calories_consumed'] ?? 0;
        $caloriesBurned = $this->summary['calories_burned'] ?? 0;
        $netCalories = $caloriesConsumed - $caloriesBurned;
        $mealCount = $this->summary['meal_count'] ?? 0;
        $exerciseCount = $this->summary['exercise_count'] ?? 0;

        return (new MailMessage)
            ->subject('오늘의 건강 리포트')
            ->greeting('안녕하세요, ' . $notifiable->name . '님!')
            ->line('오늘 하루 수고하셨습니다. 오늘의 건강 리포트입니다.')
            ->line("- 섭취 칼로리: {$caloriesConsumed} kcal ({$mealCount}끼)")
            ->line("- 소모 칼로리: {$caloriesBurned} kcal ({$exerciseCount}회 운동)")
            ->line("- 순 칼로리: {$netCalories} kcal")
            ->action('자세히 보기', url('/dashboard'))
            ->line('내일도 건강한 하루 보내세요!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'daily_summary',
            'title' => '오늘의 건강 리포트',
            'message' => '오늘의 활동 요약을 확인하세요.',
            'summary' => $this->summary,
        ];
    }
}
