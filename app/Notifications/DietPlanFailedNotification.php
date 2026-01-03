<?php

namespace App\Notifications;

use App\Models\DietPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DietPlanFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public DietPlan $dietPlan
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('식단 플랜 생성에 문제가 발생했습니다')
            ->greeting('안녕하세요, ' . $notifiable->name . '님')
            ->line('죄송합니다. 요청하신 식단 플랜 생성 중 문제가 발생했습니다.')
            ->line('잠시 후 다시 시도해 주시거나, 문제가 지속될 경우 고객센터로 문의해 주세요.')
            ->action('다시 시도하기', url('/diet-plan/generate'))
            ->line('불편을 드려 죄송합니다.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'diet_plan_failed',
            'diet_plan_id' => $this->dietPlan->id,
            'message' => '식단 플랜 생성에 실패했습니다. 다시 시도해 주세요.',
        ];
    }
}
