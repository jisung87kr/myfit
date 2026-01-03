<?php

namespace App\Notifications;

use App\Models\DietPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DietPlanReadyNotification extends Notification implements ShouldQueue
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
            ->subject('식단 플랜이 준비되었습니다!')
            ->greeting('안녕하세요, ' . $notifiable->name . '님!')
            ->line('요청하신 맞춤 식단 플랜이 생성되었습니다.')
            ->line('기간: ' . $this->dietPlan->start_date->format('Y-m-d') . ' ~ ' . $this->dietPlan->end_date->format('Y-m-d'))
            ->line('일일 목표 칼로리: ' . number_format($this->dietPlan->target_calories_per_day) . 'kcal')
            ->action('식단 플랜 확인하기', url('/diet-plan'))
            ->line('건강한 식단 관리를 응원합니다!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'diet_plan_ready',
            'diet_plan_id' => $this->dietPlan->id,
            'message' => '맞춤 식단 플랜이 준비되었습니다.',
            'start_date' => $this->dietPlan->start_date->format('Y-m-d'),
            'end_date' => $this->dietPlan->end_date->format('Y-m-d'),
        ];
    }
}
