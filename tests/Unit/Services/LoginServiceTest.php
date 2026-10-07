<?php

namespace Tests\Unit\Services;

use App\Models\Otp;
use App\Models\User;
use App\Notifications\OtpNotification;
use App\Services\LoginService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LoginServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ログインIDとパスワードが一致する場合に対象ユーザーを取得できることを検証する。
     */
    public function test_find_user_returns_user_for_valid_credentials(): void
    {
        $user = User::factory()->create([
            'login_id' => 'test-user',
            'password' => Hash::make('correct-password'),
        ]);
        $service = app(LoginService::class);

        $result = $service->findUser('test-user', 'correct-password');

        $this->assertTrue($user->is($result));
    }

    /**
     * パスワードが不一致またはログインIDが存在しない場合にユーザーを取得できないことを検証する。
     */
    public function test_find_user_returns_null_for_invalid_credentials(): void
    {
        User::factory()->create([
            'login_id' => 'test-user',
            'password' => Hash::make('correct-password'),
        ]);
        $service = app(LoginService::class);

        $this->assertNull($service->findUser('test-user', 'wrong-password'));
        $this->assertNull($service->findUser('unknown-user', 'correct-password'));
    }

    /**
     * OTP発行時に対象ユーザーの既存OTPだけが削除され、要件を満たすOTPがハッシュ保存・メール通知されることを検証する。
     */
    public function test_issue_otp_replaces_existing_otps_and_sends_notification(): void
    {
        Notification::fake();
        $user = User::factory()->create(['login_id' => 'test-user']);
        $otherUser = User::factory()->create(['login_id' => 'other-user']);
        Otp::factory()->count(2)->create(['login_id' => $user->login_id]);
        $otherOtp = Otp::factory()->create(['login_id' => $otherUser->login_id]);
        $service = app(LoginService::class);

        $this->freezeTime(function () use ($user, $otherOtp, $service): void {
            $service->issueOtp($user);

            $otpRecord = Otp::query()->where('login_id', $user->login_id)->sole();
            $plainOtp = null;
            Notification::assertSentTo(
                $user,
                OtpNotification::class,
                function (OtpNotification $notification) use (&$plainOtp): bool {
                    $plainOtp = $notification->otp;

                    return true;
                },
            );

            $this->assertDatabaseCount('otps', 2);
            $this->assertDatabaseHas('otps', ['id' => $otherOtp->id]);
            $this->assertIsString($plainOtp);
            $this->assertMatchesRegularExpression(
                '/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[!+\-*\_#%])[A-Za-z0-9!+\-*\_#%]{8}$/',
                $plainOtp,
            );
            $this->assertNotSame($plainOtp, $otpRecord->otp);
            $this->assertTrue(Hash::check($plainOtp, $otpRecord->otp));
            $this->assertSame(now()->addMinutes(10)->timestamp, $otpRecord->expires_at->timestamp);
        });
    }
}
