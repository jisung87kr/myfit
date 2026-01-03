<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MealReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $mealType
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
        $mealName = match ($this->mealType) {
            'breakfast' => '아침',
            'lunch' => '점심',
            'dinner' => '저녁',
            default => '식사',
        };

        return (new MailMessage)
            ->subject("{$mealName} 식사 시간입니다!")
            ->greeting('안녕하세요, ' . $notifiable->name . '님!')
            ->line("{$mealName} 식사 시간입니다. 건강한 식사를 하셨나요?")
            ->action('식사 기록하기', url('/daily-logs/meals'))
            ->line('식사를 기록하면 더 정확한 영양 분석을 받을 수 있습니다.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $mealName = match ($this->mealType) {
            'breakfast' => '아침',
            'lunch' => '점심',
            'dinner' => '저녁',
            default => '식사',
        };

        return [
            'type' => 'meal_reminder',
            'meal_type' => $this->mealType,
            'title' => "{$mealName} 식사 시간",
            'message' => "{$mealName} 식사를 기록해 주세요.",
        ];
    }
}
