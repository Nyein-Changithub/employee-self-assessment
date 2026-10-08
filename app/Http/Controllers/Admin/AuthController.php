<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check() && ($home = Auth::user()->homeRoute())) {
            return redirect()->route($home);
        }

        return view('admin.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $attempted = Auth::attempt(
            [
                'email' => $credentials['email'],
                'password' => $credentials['password'],
                fn ($query) => $query->whereHas('roles'),
            ],
            $request->boolean('remember')
        );

        if (! $attempted) {
            return back()->withErrors(['email' => __('These credentials do not match our records.')])
                ->onlyInput('email');
        }

        $home = Auth::user()->homeRoute();

        // A role with no permissions at all has nowhere to land.
        if (! $home) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors(['email' => __('Your account has no permissions assigned. Contact an administrator.')])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route($home));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
