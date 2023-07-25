<?php

namespace App\Http\Middleware;

use Closure;
use Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && Auth::user()->user_type == 'admin') {
            $sessionStartTime = Session::get('session_start_time');
            $sessionLifetime = Session::get('session_lifetime');

            if (time() - $sessionStartTime > $sessionLifetime * 60) {
                Auth::logout();
                Session::forget('session_start_time');
                Session::forget('session_lifetime');
                return redirect('/');
            }
            Session::put('session_start_time', time());

            return $next($request);
        }
        return redirect()->route('/');
    }
}
