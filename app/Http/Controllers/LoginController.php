<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\LoginService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('login');
    }

    public function login(LoginRequest $request, LoginService $loginService): RedirectResponse
    {
        $user = $loginService->findUser(
            $request->string('login_id')->toString(),
            $request->string('password')->toString(),
        );

        if ($user === null) {
            return back()
                ->withInput($request->only('login_id'))
                ->withErrors(['login_id' => 'ログインIDまたはパスワードが違います']);
        }

        $loginService->issueOtp($user);

        $request->session()->regenerate();
        $request->session()->put('pending_login_id', $user->login_id);
        $request->session()->put('pending_remember', $request->boolean('remember'));

        return to_route('otp.show')->with('otp_page_available', true);
    }
}
