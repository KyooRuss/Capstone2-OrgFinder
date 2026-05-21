<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check() && Auth::user()->isSuperAdmin()) {
            return redirect()->route('super-admin.organizations.index');
        }

        return view('super-admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if (!$user->isSuperAdmin()) {
                Auth::logout();
                return back()->withErrors(['email' => 'You do not have super admin access.']);
            }

            if (!$user->isActive()) {
                Auth::logout();
                return back()->withErrors(['email' => 'Your account has been blocked.']);
            }

            $request->session()->regenerate();
            return redirect()->route('super-admin.organizations.index');
        }

        return back()->withErrors(['email' => 'Invalid email or password.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('super-admin.login');
    }
}
