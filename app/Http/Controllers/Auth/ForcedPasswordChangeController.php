<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ForcedPasswordChangeController extends Controller
{
    /**
     * Show the required password change form.
     */
    public function edit(Request $request): View|RedirectResponse
    {
        if (! $request->user()?->must_change_password) {
            return redirect()->route('dashboard');
        }

        return view('auth.force-change-password');
    }
}
