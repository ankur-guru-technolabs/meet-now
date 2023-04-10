<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session;

class LoginController extends Controller
{
    //
    public function showLoginForm()
    {
        if (auth()->check() && Auth::user()->user_type == 'admin') {
            return redirect('dashboard');
        }
        return view('admin.login');
    }
    
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $remember_me = $request->has('remember_me');
        if (Auth::attempt($credentials)) {
            $lifetime = $remember_me ? 20160 : 60;
            Session::put('session_start_time', time());
            Session::put('session_lifetime', $lifetime);
            return redirect("dashboard");
        }

        return back()->withErrors([
            'error' => 'The provided credentials do not match our records.',
        ]);
    }

}
