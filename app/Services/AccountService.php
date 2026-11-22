<?php

namespace App\Services;

use App\Models\User;

class AccountService
{
    /**
     * @param  array{name: string, email: string, login_id: string}  $attributes
     */
    public function update(User $user, array $attributes): void
    {
        $user->update($attributes);
    }
}
