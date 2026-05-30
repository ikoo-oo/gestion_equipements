<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckHR
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is logged in AND has 'hr' role
        if (Auth::check() && Auth::user()->role === 'hr') {
            return $next($request);
        }

        // Not authorized - redirect to login
        return redirect()->route('login')->withErrors([
            'access' => 'Accès non autorisé. Vous devez être RH.'
        ]);
    }
}
