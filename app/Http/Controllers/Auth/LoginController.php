<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Show the login form
     */
    public function showLogin()
    {
        // If already logged in, redirect to their dashboard
        if (Auth::check()) {
            return $this->redirectToDashboard();
        }

        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        // Validate input
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ], [
            'email.required' => 'L\'email est requis.',
            'email.email' => 'L\'email doit être valide.',
            'password.required' => 'Le mot de passe est requis.'
        ]);

        // Attempt to log the user in - check the database
        if (Auth::attempt($credentials)) {
            // Regenerate session (security)
            $request->session()->regenerate();

            // Redirect based on role
            return $this->redirectToDashboard();
        }

        // Login failed - return with error
        return back()->withErrors([
            'email' => 'Email ou mot de passe incorrect.'
        ])->withInput($request->only('email'));
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Vous êtes déconnecté.');
    }

    /**
     * Redirect user to appropriate dashboard based on role
     */
    protected function redirectToDashboard()
    {
        $user = Auth::user();

        return match($user->role) {
            'hr' => redirect()->route('hr.dashboard'),
            'it_manager' => redirect()->route('it-manager.dashboard'),
            'technician' => redirect()->route('technician.dashboard'),
            default => redirect()->route('login')->withErrors(['error' => 'Rôle non reconnu.'])
        };
    }








    /**
     * Show forgot password form
     */
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle forgot password (just for show - doesn't actually send email)
     */
    public function forgotPassword(Request $request)
    {
        // Validate email format
        $request->validate([
            'email' => 'required|email'
        ], [
            'email.required' => 'L\'email est requis.',
            'email.email' => 'L\'email doit être valide.'
        ]);

        // Just show success message (doesn't actually do anything)
        return back()->with('success', ' Un email de réinitialisation a été envoyé à ' . $request->email . ' si ce compte existe.');
    }
}
