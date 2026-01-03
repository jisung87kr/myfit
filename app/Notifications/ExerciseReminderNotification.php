<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExerciseReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct() {}

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
        return (new MailMessage)
            ->subject('오늘의 운동을 시작하세요!')
            ->greeting('안녕하세요, ' . $notifiable->name . '님!')
            ->line('오늘 운동하셨나요? 건강한 하루를 위해 운동을 시작해보세요!')
            ->action('운동 기록하기', url('/daily-logs/exercises'))
            ->line('꾸준한 운동이 건강의 비결입니다.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'exercise_reminder',
            'title' => '운동 알림',
            'message' => '오늘의 운동을 기록해 주세요.',
        ];
    }
}
