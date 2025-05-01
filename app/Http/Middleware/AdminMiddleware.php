<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Http\Foundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (auth()->check() && auth()->user()->role->role_name === 'admin') {
            return $next($request);
        }

        return redirect()->route('dashboard')->with('error', 'You do not have admin access.');
    }
}
