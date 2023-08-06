<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;



class LoginController extends Controller
{
    public function authenticate(Request $request) //: RedirectResponse
    {
        $credentials = $request->validate([
            'accountNo' => ['required'],
            'password' => ['required'],
        ]);
        // dd($credentials);
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return response()->json([
                'response' => 'success',
                'url' => Session::get('url.intended', url('/home'))
            ]);
        }
        return response()->json([
            'response' => 'fail',
            'error' => 'Invalid Credentials',
        ]);
    }


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
        error_log('created user');
    }
}
