<?php

namespace App\Services;

use App\Models\Otp;
use App\Models\User;
use App\Notifications\OtpNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LoginService
{
    private const OTP_LENGTH = 8;

    private const OTP_LIFETIME_MINUTES = 10;

    private const SYMBOLS = '!+-*_#%';

    public function findUser(string $loginId, string $password): ?User
    {
        $user = User::query()->where('login_id', $loginId)->first();

        if ($user === null || ! Hash::check($password, $user->password)) {
            return null;
        }

        return $user;
    }

    public function issueOtp(User $user): void
    {
        $plainOtp = $this->generateOtp();

        DB::transaction(function () use ($user, $plainOtp): void {
            Otp::query()->where('login_id', $user->login_id)->delete();
            Otp::query()->create([
                'login_id' => $user->login_id,
                'otp' => Hash::make($plainOtp),
                'expires_at' => now()->addMinutes(self::OTP_LIFETIME_MINUTES),
            ]);
        });

        $user->notify(new OtpNotification($plainOtp));
    }

    private function generateOtp(): string
    {
        $characters = [
            $this->randomCharacter('ABCDEFGHIJKLMNOPQRSTUVWXYZ'),
            $this->randomCharacter('abcdefghijklmnopqrstuvwxyz'),
        ];
        $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

        while (count($characters) < self::OTP_LENGTH - 2) {
            $characters[] = $this->randomCharacter($letters);
        }

        $characters[] = $this->randomCharacter('0123456789');
        $characters[] = $this->randomCharacter(self::SYMBOLS);

        for ($index = count($characters) - 1; $index > 0; $index--) {
            $swapIndex = random_int(0, $index);
            [$characters[$index], $characters[$swapIndex]] = [$characters[$swapIndex], $characters[$index]];
        }

        return implode('', $characters);
    }

    private function randomCharacter(string $characters): string
    {
        return $characters[random_int(0, strlen($characters) - 1)];
    }
}
