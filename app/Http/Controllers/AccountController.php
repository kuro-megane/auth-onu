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
        $accountService->update($request->authenticatedUser(), $request->accountAttributes());

        return to_route('menu')->with('status', 'アカウント情報を更新しました。');
    }
}
