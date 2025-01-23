<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) { // Assuming `is_admin` is a field in your users table
            return $next($request);
        }

        return redirect('/admin/login'); // Redirect to the home page or login page
    }
}
