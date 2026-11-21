<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\SendResetLinkRequest;
use App\Services\PasswordResetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function request(): View
    {
        return view('passwords.request');
    }

    public function email(SendResetLinkRequest $request, PasswordResetService $passwordResetService): RedirectResponse
    {
        $passwordResetService->sendResetLink($request->string('email')->toString());

        return back()->with(
            'status',
            '入力されたメールアドレス宛に、パスワード再設定のご案内を送信しました。メールが届かない場合は、入力内容をご確認ください。',
        );
    }

    public function reset(Request $request, string $token): View
    {
        return view('passwords.reset', [
            'token' => $token,
            'email' => $request->string('email')->toString(),
        ]);
    }

    public function update(ResetPasswordRequest $request, PasswordResetService $passwordResetService): RedirectResponse
    {
        $reset = $passwordResetService->reset(
            $request->string('email')->toString(),
            $request->string('password')->toString(),
            $request->string('token')->toString(),
        );

        if (! $reset) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'パスワード再設定リンクが無効か、有効期限が切れています。']);
        }

        return to_route('login')->with('status', 'パスワードを変更しました。新しいパスワードでログインしてください。');
    }
}
