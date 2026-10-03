<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('login');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('login_id', 'password');


        // ログイン失敗
        return back()->withErrors([
            'login_id' => 'ログインIDまたはパスワードが正しくありません。',
        ]);
    }
}
