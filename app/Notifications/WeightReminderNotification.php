<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WeightReminderNotification extends Notification implements ShouldQueue
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
            ->subject('체중을 기록해 주세요!')
            ->greeting('안녕하세요, ' . $notifiable->name . '님!')
            ->line('오늘의 체중을 기록해 보세요. 꾸준한 기록이 목표 달성의 첫걸음입니다!')
            ->action('체중 기록하기', url('/weight-logs'))
            ->line('매일 같은 시간에 측정하면 더 정확한 추이를 확인할 수 있습니다.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'weight_reminder',
            'title' => '체중 기록 알림',
            'message' => '오늘의 체중을 기록해 주세요.',
        ];
    }
}
