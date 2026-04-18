<?php

namespace Modules\SponsorPortal\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SponsorAuthenticate
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth('sponsor')->check()) {
            return redirect()->route('sponsor.login')->with('error', __('Please log in to access the sponsor portal.'));
        }
        if (auth('sponsor')->user()->status !== 'active') {
            auth('sponsor')->logout();
            return redirect()->route('sponsor.login')->with('error', __('Your account is inactive.'));
        }
        return $next($request);
    }
}
