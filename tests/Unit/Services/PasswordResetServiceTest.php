<?php

namespace Tests\Unit\Services;

use App\Models\Otp;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use App\Services\PasswordResetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 有効なトークンでパスワードが更新され、対象ユーザーのセッションとOTPだけが破棄されることを検証する。
     */
    public function test_reset_updates_password_and_revokes_authentication_state(): void
    {
        Notification::fake();
        $user = User::factory()->create([
            'login_id' => 'reset-user',
            'remember_token' => 'old-remember-token',
        ]);
        $otherUser = User::factory()->create(['login_id' => 'other-user']);
        Otp::factory()->create(['login_id' => $user->login_id]);
        $otherOtp = Otp::factory()->create(['login_id' => $otherUser->login_id]);
        $this->createSession('reset-session', $user->id);
        $this->createSession('other-session', $otherUser->id);
        $service = app(PasswordResetService::class);
        $token = $this->issueToken($service, $user);

        $result = $service->reset($user->email, 'NewPassword1!', $token);

        $this->assertTrue($result);
        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword1!', $user->password));
        $this->assertNotSame('old-remember-token', $user->remember_token);
        $this->assertDatabaseMissing('sessions', ['id' => 'reset-session']);
        $this->assertDatabaseHas('sessions', ['id' => 'other-session']);
        $this->assertDatabaseMissing('otps', ['login_id' => $user->login_id]);
        $this->assertDatabaseHas('otps', ['id' => $otherOtp->id]);
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
    }

    /**
     * 発行から10分を超えたトークンではパスワードが更新されないことを検証する。
     */
    public function test_reset_rejects_token_after_ten_minutes(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $service = app(PasswordResetService::class);
        $token = $this->issueToken($service, $user);
        $this->travel(601)->seconds();

        $result = $service->reset($user->email, 'NewPassword1!', $token);

        $this->assertFalse($result);
        $this->assertFalse(Hash::check('NewPassword1!', $user->fresh()->password));
    }

    private function issueToken(PasswordResetService $service, User $user): string
    {
        $token = '';
        $service->sendResetLink($user->email);
        Notification::assertSentTo(
            $user,
            ResetPasswordNotification::class,
            function (ResetPasswordNotification $notification) use (&$token): bool {
                $token = $notification->token;

                return true;
            },
        );

        return $token;
    }

    private function createSession(string $id, int $userId): void
    {
        DB::table('sessions')->insert([
            'id' => $id,
            'user_id' => $userId,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'payload' => '',
            'last_activity' => now()->timestamp,
        ]);
    }
}
