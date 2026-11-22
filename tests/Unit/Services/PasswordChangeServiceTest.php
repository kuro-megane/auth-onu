<?php

namespace Tests\Unit\Services;

use App\Models\Otp;
use App\Models\User;
use App\Services\PasswordChangeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordChangeServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * パスワード変更時にremember tokenを更新し、対象ユーザーの全セッションとOTPだけを破棄することを検証する。
     */
    public function test_change_updates_password_and_revokes_authentication_state(): void
    {
        $user = User::factory()->create([
            'login_id' => 'change-user',
            'remember_token' => 'old-remember-token',
        ]);
        $otherUser = User::factory()->create(['login_id' => 'other-user']);
        Otp::factory()->create(['login_id' => $user->login_id]);
        $otherOtp = Otp::factory()->create(['login_id' => $otherUser->login_id]);
        $this->createSession('change-session-1', $user->id);
        $this->createSession('change-session-2', $user->id);
        $this->createSession('other-session', $otherUser->id);

        app(PasswordChangeService::class)->change($user, 'NewPassword2#');

        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword2#', $user->password));
        $this->assertNotSame('old-remember-token', $user->remember_token);
        $this->assertDatabaseMissing('sessions', ['id' => 'change-session-1']);
        $this->assertDatabaseMissing('sessions', ['id' => 'change-session-2']);
        $this->assertDatabaseHas('sessions', ['id' => 'other-session']);
        $this->assertDatabaseMissing('otps', ['login_id' => $user->login_id]);
        $this->assertDatabaseHas('otps', ['id' => $otherOtp->id]);
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
