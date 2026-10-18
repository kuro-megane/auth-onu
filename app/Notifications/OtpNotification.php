<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OtpNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly string $otp) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('ワンタイムパスワードのお知らせ')
            ->greeting($notifiable->name.' 様')
            ->line('ログイン用のワンタイムパスワードは以下です。')
            ->line($this->otp)
            ->line('有効期限は発行から10分です。');
    }
}
