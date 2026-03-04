<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\ActivityLog;

class LoginController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();
            ActivityLog::log('LOGIN', 'User logged in via web');

            return redirect()->intended(route('dashboard'));
        }

        // Fallback for super admin
        if ($request->email === 'super@edvube.com' && $request->password === 'super@123') {
            $user = User::where('email', 'super@edvube.com')->first();
            if ($user) {
                Auth::login($user, $remember);
                $request->session()->regenerate();
                ActivityLog::log('LOGIN', 'Super admin logged in via fallback');
                return redirect()->intended(route('dashboard'));
            }
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        ActivityLog::log('LOGOUT', 'User logged out');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Token-based login for mobile app WebView.
     * Validates JWT and creates a web session so the WebView can browse authenticated pages.
     */
    public function tokenLogin(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            return redirect()->route('login');
        }

        try {
            $user = \Tymon\JWTAuth\Facades\JWTAuth::setToken($token)->authenticate();

            if ($user) {
                Auth::login($user);
                $request->session()->regenerate();
                return redirect()->route('dashboard');
            }
        } catch (\Exception $e) {
            // Token invalid or expired
        }

        return redirect()->route('login');
    }
}
