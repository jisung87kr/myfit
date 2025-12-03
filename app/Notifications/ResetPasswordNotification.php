<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private string $token
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $resetUrl = $this->getResetUrl($notifiable);

        return (new MailMessage)
            ->subject('비밀번호 재설정 요청')
            ->greeting('안녕하세요, ' . $notifiable->name . '님')
            ->line('비밀번호 재설정 요청을 받았습니다.')
            ->line('아래 버튼을 클릭하여 비밀번호를 재설정하세요.')
            ->action('비밀번호 재설정', $resetUrl)
            ->line('이 링크는 60분 동안 유효합니다.')
            ->line('비밀번호 재설정을 요청하지 않으셨다면 이 이메일을 무시하세요.')
            ->salutation('감사합니다.');
    }

    /**
     * Get password reset URL
     *
     * @param object $notifiable
     * @return string
     */
    protected function getResetUrl(object $notifiable): string
    {
        // For API, return token directly or construct frontend URL
        // This would typically be your frontend app URL
        $frontendUrl = config('app.frontend_url', config('app.url'));

        return $frontendUrl . '/reset-password?token=' . $this->token . '&email=' . urlencode($notifiable->email);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'token' => $this->token,
        ];
    }
}
