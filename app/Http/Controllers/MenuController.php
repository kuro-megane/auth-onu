<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function show(): View
    {
        $familyApps = collect(config('family_apps', []))
            ->filter(fn (array $app): bool => is_string($app['url']) && trim($app['url']) !== '')
            ->values()
            ->all();

        return view('menu', ['familyApps' => $familyApps]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('login');
    }
}
