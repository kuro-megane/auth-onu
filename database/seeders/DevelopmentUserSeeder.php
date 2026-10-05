<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DevelopmentUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->firstOrNew(['login_id' => 'demo']);
        $user->forceFill([
            'name' => '動作確認ユーザー',
            'email' => 'demo@example.com',
            'password' => Hash::make('DemoPassword1!'),
            'email_verified_at' => now(),
            'is_master' => false,
            'deleted_at' => null,
        ])->save();
    }
}
