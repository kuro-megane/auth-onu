<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Otp;
use App\Models\User;
use App\Notifications\OtpNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthenticationFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 誤ったログイン情報ではエラーが表示され、未認証状態が維持されることを検証する。
     */
    public function test_login_with_invalid_credentials_returns_error(): void
    {
        User::factory()->create([
            'login_id' => 'test-user',
            'password' => Hash::make('correct-password'),
        ]);

        $response = $this->from(route('login'))->post(route('login.attempt'), [
            'login_id' => 'test-user',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors([
            'login_id' => 'ログインIDまたはパスワードが違います',
        ]);
        $this->assertGuest();
    }

    /**
     * 正しいログイン情報により既存OTPが削除され、要件を満たすOTPがハッシュ保存・メール通知されることを検証する。
     */
    public function test_valid_login_creates_hashed_otp_deletes_existing_otps_and_sends_email(): void
    {
        Notification::fake();
        $user = User::factory()->create([
            'login_id' => 'test-user',
            'password' => Hash::make('password'),
        ]);
        Otp::factory()->count(2)->create(['login_id' => $user->login_id]);
        $issuedAt = now();

        $response = $this->post(route('login.attempt'), [
            'login_id' => $user->login_id,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('otp.show'));
        $response->assertSessionHas('pending_login_id', $user->login_id);
        $this->assertGuest();
        $this->assertDatabaseCount('otps', 1);

        $otpRecord = Otp::query()->sole();
        $plainOtp = null;
        Notification::assertSentTo(
            $user,
            OtpNotification::class,
            function (OtpNotification $notification) use (&$plainOtp): bool {
                $plainOtp = $notification->otp;

                return true;
            },
        );

        $this->assertIsString($plainOtp);
        $this->assertMatchesRegularExpression('/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[!+\-*\_#%])[A-Za-z0-9!+\-*\_#%]{8}$/', $plainOtp);
        $this->assertTrue(Hash::check($plainOtp, $otpRecord->otp));
        $this->assertNotSame($plainOtp, $otpRecord->otp);
        $this->assertTrue($otpRecord->expires_at->between(
            $issuedAt->copy()->addMinutes(10)->subSecond(),
            now()->addMinutes(10),
        ));
    }

    /**
     * OTP認証画面は最初の表示だけ許可され、リロードするとログイン画面へ戻ることを検証する。
     */
    public function test_otp_page_refresh_redirects_to_login(): void
    {
        $this->withSession([
            'otp_page_available' => true,
            'pending_login_id' => 'test-user',
        ])
            ->get(route('otp.show'))
            ->assertOk()
            ->assertViewIs('otp');

        $this->get(route('otp.show'))->assertRedirect(route('login'));
        $this->assertFalse(session()->has('pending_login_id'));
    }

    /**
     * 有効なOTPでユーザーがログインし、OTPと認証待ちセッションが破棄されてメニューへ遷移することを検証する。
     */
    public function test_correct_otp_logs_user_in_and_redirects_to_menu(): void
    {
        $user = User::factory()->create(['login_id' => 'test-user']);
        Otp::factory()->create([
            'login_id' => $user->login_id,
            'otp' => Hash::make('Abcdef1!'),
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->withSession([
            'pending_login_id' => $user->login_id,
            'pending_remember' => false,
        ])->post(route('otp.verify'), ['otp' => 'Abcdef1!']);

        $response->assertRedirect(route('menu'));
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseMissing('otps', ['login_id' => $user->login_id]);
        $response->assertSessionMissing('pending_login_id');
    }

    /**
     * 誤ったOTPではエラーが表示され、ログインできず、再表示後のリロードでログイン画面へ戻ることを検証する。
     */
    public function test_incorrect_otp_returns_error_and_does_not_log_user_in(): void
    {
        $user = User::factory()->create(['login_id' => 'test-user']);
        Otp::factory()->create([
            'login_id' => $user->login_id,
            'otp' => Hash::make('Abcdef1!'),
        ]);

        $response = $this->followingRedirects()
            ->from(route('otp.show'))
            ->withSession(['pending_login_id' => $user->login_id])
            ->post(route('otp.verify'), ['otp' => 'Wrongp2#']);

        $response->assertOk()
            ->assertSee('ワンタイムパスワードが間違っているか、有効期限が切れています');
        $this->assertGuest();
        $this->get(route('otp.show'))->assertRedirect(route('login'));
    }

    /**
     * 有効期限切れのOTPではエラーとなり、ユーザーがログインしないことを検証する。
     */
    public function test_expired_otp_returns_error_and_does_not_log_user_in(): void
    {
        $user = User::factory()->create(['login_id' => 'test-user']);
        Otp::factory()->create([
            'login_id' => $user->login_id,
            'otp' => Hash::make('Abcdef1!'),
            'expires_at' => now()->subSecond(),
        ]);

        $response = $this->withSession(['pending_login_id' => $user->login_id])
            ->post(route('otp.verify'), ['otp' => 'Abcdef1!']);

        $response->assertSessionHasErrors([
            'otp' => 'ワンタイムパスワードが間違っているか、有効期限が切れています',
        ]);
        $this->assertGuest();
    }

    /**
     * メニューの認証制御、認証済みユーザーの遷移、およびログアウトによる認証終了を検証する。
     */
    public function test_menu_requires_authentication_and_logout_ends_session(): void
    {
        $this->get(route('menu'))->assertRedirect(route('login'));

        $user = User::factory()->create();
        $this->actingAs($user)->get(route('menu'))->assertOk()->assertViewIs('menu');
        $this->actingAs($user)->get(route('login'))->assertRedirect(route('menu'));
        $this->actingAs($user)->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
