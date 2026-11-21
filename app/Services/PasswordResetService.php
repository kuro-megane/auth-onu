<?php

namespace App\Services;

use App\Models\Otp;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetService
{
    public function sendResetLink(string $email): void
    {
        Password::sendResetLink(['email' => $email]);
    }

    public function reset(string $email, string $password, string $token): bool
    {
        $status = Password::reset(
            compact('email', 'password', 'token'),
            function (User $user, string $password): void {
                DB::transaction(function () use ($user, $password): void {
                    $user->forceFill([
                        'password' => Hash::make($password),
                        'remember_token' => Str::random(60),
                    ])->save();

                    DB::table(Config::string('session.table'))
                        ->where('user_id', $user->getKey())
                        ->delete();
                    Otp::query()->where('login_id', $user->login_id)->delete();
                });
            },
        );

        return $status === Password::PASSWORD_RESET;
    }
}
