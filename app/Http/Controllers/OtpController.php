<?php

namespace App\Http\Controllers;

use App\Http\Requests\OtpRequest;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OtpController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        $pageAvailable = $request->session()->pull('otp_page_available', false);

        if (! $pageAvailable && ! $request->session()->has('errors')) {
            $request->session()->forget(['pending_login_id', 'pending_remember']);

            return to_route('login');
        }

        return view('otp');
    }

    public function verify(OtpRequest $request, OtpService $otpService): RedirectResponse
    {
        $loginId = $request->session()->get('pending_login_id');

        if (! is_string($loginId)) {
            return to_route('login');
        }

        $user = $otpService->verify($loginId, $request->string('otp')->toString());

        if ($user === null) {
            return back()->withErrors([
                'otp' => 'ワンタイムパスワードが間違っているか、有効期限が切れています',
            ]);
        }

        Auth::login($user, (bool) $request->session()->pull('pending_remember', false));
        $request->session()->forget('pending_login_id');
        $request->session()->regenerate();

        return to_route('menu');
    }
}
