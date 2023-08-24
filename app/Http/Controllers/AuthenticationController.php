<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthenticationController extends Controller
{
    // Route: /login
    // Type: POST
    // Handles a login request
    public function login(Request $request)
    {
        // checks if credentials in the request meet the rules
        // If it fails, returns 302 response
        $credentials = $request->validate([
            'accountNo' => ['required'],
            'password' => ['required'],
        ]);

        // attemps to authenticate using the provided credentials
        // if authentication succeeds, makes a session and returns success response,
        //with redirect to the home page.
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended("/home");
        }

        // if auth fails, returns a fail response with error message
        return redirect()->back()->with([
            'customError' => 'Invalid Credentials'
        ]);
    }



    // Route: /logout
    // Type: POST
    // Logs out the currently authenticated user
    public function logout(Request $request)
    {
        // Logs out the user, and invalidates their CSRF token.
        // then redirects them back to the landing page
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->json([
            'response' => 'success',
            'url' => url('/'),
        ]);
    }
}
