<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Route: /login
    // Type: POST
    // Handles a login request
    public function authenticate(Request $request)
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
            return response()->json([
                'response' => 'success',
                'url' => Session::get('url.intended', url('/home')),
            ]);
        }
        // if auth fails, returns a fail response with error message
        return response()->json([
            'response' => 'fail',
            'error' => 'Invalid Credentials',
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



    // Route: /login/create
    // Type: GET
    // temporary function to create a single user with known credentials,
    // for testing purposes
    public function create(Request $request)
    {
        DB::table('accounts')->insert([
            'accountNo' => '123456c',
            'aType' => 'sysadmin',
            'lName' => 'Smith',
            'fNames' => 'John',
            'password' => Hash::make('testPassword7'),
            'superiorNo' => '123456a',
        ]);
    }
}
