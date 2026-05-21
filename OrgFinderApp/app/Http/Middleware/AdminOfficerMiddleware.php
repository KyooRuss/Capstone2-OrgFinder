<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOfficerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->isAdminOfficer()) {
            return redirect()->route('admin-officer.login');
        }

        if (!auth()->user()->isActive()) {
            auth()->logout();
            return redirect()->route('admin-officer.login')
                ->withErrors(['email' => 'Your account has been blocked.']);
        }

        return $next($request);
    }
}
