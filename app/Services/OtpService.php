<?php

namespace App\Services;

use App\Models\Otp;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class OtpService
{
    public function verify(string $loginId, string $plainOtp): ?User
    {
        return DB::transaction(function () use ($loginId, $plainOtp): ?User {
            $otp = Otp::query()->where('login_id', $loginId)->lockForUpdate()->first();

            if ($otp === null || $otp->expires_at->isPast() || ! Hash::check($plainOtp, $otp->otp)) {
                return null;
            }

            $user = User::query()->where('login_id', $loginId)->first();

            if ($user === null) {
                return null;
            }

            $otp->delete();

            return $user;
        });
    }
}
