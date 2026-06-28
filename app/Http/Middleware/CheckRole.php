<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Session;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, $role)
    {
        if (!Session::has('role')) {
            $redirect = ($role === 'client') ? route('client.login.page') : route('auth.login.page');
            return redirect($redirect)->with('error', 'Please login first');
        }

        if (Session::get('role') !== $role) {
            $redirect = ($role === 'client') ? route('client.login.page') : route('auth.login.page');
            return redirect($redirect)->with('error', 'Unauthorized');
        }

        return $next($request);
    }
}
