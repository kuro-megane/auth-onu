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
        $configuredApps = config('family_apps', []);
        $familyApps = [];

        if (is_array($configuredApps)) {
            foreach ($configuredApps as $app) {
                if (! is_array($app) || ! is_string($app['url'] ?? null) || trim($app['url']) === '') {
                    continue;
                }

                $familyApps[] = $app;
            }
        }

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
