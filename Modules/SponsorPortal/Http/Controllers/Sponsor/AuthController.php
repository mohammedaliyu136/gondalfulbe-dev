<?php

namespace Modules\SponsorPortal\Http\Controllers\Sponsor;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (auth('sponsor')->check()) {
            return redirect()->route('sponsor.dashboard');
        }
        return view('sponsorportal::portal.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);
        if (auth('sponsor')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            if (auth('sponsor')->user()->status !== 'active') {
                auth('sponsor')->logout();
                return back()->with('error', __('Your account is inactive.'));
            }
            return redirect()->route('sponsor.dashboard');
        }
        return back()->with('error', __('Invalid credentials.'));
    }

    public function logout(Request $request)
    {
        auth('sponsor')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('sponsor.login');
    }
}
