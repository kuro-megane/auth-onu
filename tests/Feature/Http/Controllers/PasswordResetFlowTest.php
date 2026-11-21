<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ログイン画面からパスワード再設定申請画面へ遷移できることを検証する。
     */
    public function test_login_page_links_to_password_reset_request(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSeeText('パスワードを忘れた場合')
            ->assertSee('href="'.route('password.request').'"', false);

        $this->get(route('password.request'))
            ->assertOk()
            ->assertViewIs('passwords.request');
    }

    /**
     * 登録済みメールアドレスから有効期限10分の再設定通知が送信されることを検証する。
     */
    public function test_reset_link_is_sent_to_registered_email_with_ten_minute_expiration(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $response = $this->post(route('password.email'), ['email' => $user->email]);

        $response->assertRedirect()
            ->assertSessionHas('status');
        $this->assertSame(10, config('auth.passwords.users.expire'));
        Notification::assertSentTo(
            $user,
            ResetPasswordNotification::class,
            function (ResetPasswordNotification $notification) use ($user): bool {
                $mail = $notification->toMail($user);

                $this->assertStringContainsString($notification->token, $mail->actionUrl);
                $this->assertStringContainsString('email=', $mail->actionUrl);
                $this->assertContains('このリンクの有効期限は10分です。', $mail->outroLines);

                return true;
            },
        );
    }

    /**
     * 未登録メールアドレスでも登録済みの場合と同じ応答となることを検証する。
     */
    public function test_unknown_email_receives_same_response_without_notification(): void
    {
        Notification::fake();

        $response = $this->post(route('password.email'), ['email' => 'unknown@example.com']);

        $response->assertRedirect()
            ->assertSessionHas('status');
        Notification::assertNothingSent();
    }

    /**
     * 不正なメールアドレスでは再設定メールが送信されず入力エラーになることを検証する。
     */
    public function test_invalid_email_is_rejected(): void
    {
        Notification::fake();

        $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => 'invalid-address'])
            ->assertRedirect(route('password.request'))
            ->assertSessionHasErrors('email');

        Notification::assertNothingSent();
    }

    /**
     * 再設定画面にメールアドレスとトークンが引き継がれることを検証する。
     */
    public function test_reset_form_receives_token_and_email(): void
    {
        $response = $this->get(route('password.reset', [
            'token' => 'reset-token',
            'email' => 'user@example.com',
        ]));

        $response->assertOk()
            ->assertViewIs('passwords.reset')
            ->assertViewHas('token', 'reset-token')
            ->assertViewHas('email', 'user@example.com');
    }

    /**
     * パスワード要件を満たさない入力では更新されないことを検証する。
     */
    public function test_weak_password_is_rejected(): void
    {
        $this->post(route('password.update'), [
            'token' => 'token',
            'email' => 'user@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasErrors('password');
    }
}
