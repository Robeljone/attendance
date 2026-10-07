<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the employee login view.
     */
    public function create(): View
    {
        return view('auth.login', [
            'portal' => 'employee',
        ]);
    }

    /**
     * Display the staff (HR / manager / admin) login view.
     */
    public function createStaff(): View
    {
        return view('auth.login', [
            'portal' => 'staff',
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        if ($request->user()?->must_change_password) {
            return redirect()->route('password.change-required');
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $wasStaff = $request->user()?->isStaff() ?? false;

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route($wasStaff ? 'admin.login' : 'login');
    }
}
