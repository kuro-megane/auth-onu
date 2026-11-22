<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAccountRequest;
use App\Models\User;
use App\Services\AccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function edit(Request $request): View
    {
        return view('account.edit', ['user' => $request->user()]);
    }

    public function update(UpdateAccountRequest $request, AccountService $accountService): RedirectResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(401);
        }

        $accountService->update($user, [
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'login_id' => $request->string('login_id')->toString(),
        ]);

        return to_route('menu')->with('status', 'アカウント情報を更新しました。');
    }
}
