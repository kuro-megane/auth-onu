<?php

namespace Tests\Unit\Services;

use App\Models\Otp;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OtpServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_otp_is_single_use(): void
    {
        $user = User::factory()->create(['login_id' => 'test-user']);
        Otp::factory()->create([
            'login_id' => $user->login_id,
            'otp' => Hash::make('Abcdef1!'),
        ]);
        $service = app(OtpService::class);

        $this->assertTrue($user->is($service->verify($user->login_id, 'Abcdef1!')));
        $this->assertNull($service->verify($user->login_id, 'Abcdef1!'));
    }
}
