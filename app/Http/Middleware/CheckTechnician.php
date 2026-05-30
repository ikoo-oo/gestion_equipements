<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckTechnician
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->role === 'technician') {
            return $next($request);
        }

        return redirect()->route('login')->withErrors([
            'access' => 'Accès non autorisé. Vous devez être Technicien.'
        ]);
    }
}
