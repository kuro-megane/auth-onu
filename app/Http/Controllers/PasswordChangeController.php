<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Models\User;
use App\Services\PasswordChangeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PasswordChangeController extends Controller
{
    public function edit(): View
    {
        return view('account.password');
    }

    public function update(
        ChangePasswordRequest $request,
        PasswordChangeService $passwordChangeService,
    ): RedirectResponse {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(401);
        }

        $passwordChangeService->change(
            $user,
            $request->string('password')->toString(),
        );

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('login')->with('status', 'パスワードを変更しました。新しいパスワードでログインしてください。');
    }
}
