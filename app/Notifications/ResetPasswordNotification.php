<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly string $token) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $expiration = Config::integer('auth.passwords.users.expire');
        $url = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage)
            ->subject('パスワード再設定のお知らせ')
            ->greeting($notifiable->name.' 様')
            ->line('パスワード再設定の申請を受け付けました。')
            ->action('パスワードを再設定する', $url)
            ->line("このリンクの有効期限は{$expiration}分です。")
            ->line('この申請に心当たりがない場合は、操作する必要はありません。');
    }
}
