<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Admin login only — players never sign in. */
class AuthController extends Controller
{
    public function show()
    {
        return Auth::check() && Auth::user()->is_admin
            ? redirect()->route('gameset.boards.index')
            : view('auth.login');
    }

    public function login(Request $request)
    {
        $creds = $request->validate(['email' => 'required|email', 'password' => 'required|string']);

        if (!Auth::attempt($creds + ['is_admin' => true], $request->boolean('remember'))) {
            return back()->withInput($request->only('email'))->withErrors(['email' => 'Those details are not right.']);
        }
        $request->session()->regenerate();

        return redirect()->intended(route('gameset.boards.index'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
