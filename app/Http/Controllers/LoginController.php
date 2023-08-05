<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;



class LoginController extends Controller
{
    public function authenticate(Request $request): RedirectResponse
    {
        error_log('entered auth functino');

        $credentials = $request->validate([
            'accountNo' => ['required'],
            'pswd' => ['required'],
        ]);
        error_log('after validation');


        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('home');
        }

        return back()->withErrors([
            'accountNo' => 'Provided credentials do not match',
        ])->onlyInput('accountNo');
    }
}
