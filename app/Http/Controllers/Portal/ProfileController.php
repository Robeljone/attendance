<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $employee = $request->user()
            ->employee()
            ->with(['department', 'workSchedules', 'user', 'educations', 'documents'])
            ->first();

        abort_unless($employee, 403);

        return view('portal.profile.show', compact('employee'));
    }
}
