<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Response;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use DateTime;


class AuthenticationController extends Controller
{
    // Route: /login
    // Type: POST
    // Function: Processes a login request
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'accountNo' => ['required'],
            'password' => ['required'],
        ]);

        // Validate credentials, create a session and redirect the user to the home page
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended("/home");
        }

        // If credentials could not be validated, redirect them back with an error
        return redirect()->back()->with([
            'customError' => 'Invalid Credentials'
        ]);
    }



    // Route: /logout
    // Type: POST
    // Function: Logs out the currently authenticated user
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



    // Route: /reset-password
    // Type: POST
    // Function: Request a password reset token + email
    public function reset(Request $request)
    {
        // dd('here');
        $request->validate([
            'accountNo' => 'required'
        ]);

        // Attempt to generate a token and send a link to the user
        $status = Password::sendResetLink(
            $request->only('accountNo')
        );
        if ($status == Password::RESET_LINK_SENT) {
            return response()->json([
                'status' => __($status),
            ]);
        }

        // Throw exception if something fails
        throw ValidationException::withMessages([
            'accountNo' => [trans($status)],
        ]);
    }



    // Route: /login/create
    // Type: GET
    // Function: render the password reset page, passing it the necessary paramters
    public function create(Request $request): Response
    {
        return Inertia::render('Reset', [
            'email' => $request->email,
            'token' => $request->route('token'),
        ]);
    }



    // Route: /update-password
    // Type: POST
    // Purpose: Attempt to reset a users password
    public function store(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'accountNo' => 'required',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Attempt password reset
        $status = Password::reset(
            $request->only('accountNo', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                ])->save();
                event(new PasswordReset($user));
            }
        );

        // return with successful response
        if ($status == Password::PASSWORD_RESET) {
            return response()->json([
                'status' => __($status),
            ]);
        }

        // excpetion if it doesn't work
        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }



    // Route: /change-password
    // Type: POST
    // Purpose: Handle a password reset from the home page, where the user has not come
    //          from an email, and therefore does not already have a token generated.
    public function homeStore(Request $request)
    {
        $request->validate([
            'accountNo' => 'required',
            'currentPassword' => 'required',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // check if the current password is correct
        $currentPassword = $request->only('currentPassword')['currentPassword'];
        $user = Auth::user();
        if (!(Hash::check($currentPassword, $user->password))) {
            throw ValidationException::withMessages([
                'email' => 'Current Password Incorrect.',
            ]);
        }

        $accountNo = $request->only('accountNo')['accountNo'];
        $password = $request->only('password')['password'];



        // Check if there is already a reset token for this account
        if ((DB::table('password_reset_tokens')
            ->where('email', '=',  $accountNo . '@curtin.edu.au.com')->first() == null)) {


            // Manually generate a new token (normally done by the method that sends the
            // password reset email)
            $newToken = app('auth.password.broker')->createToken($user);
            DB::table('password_reset_tokens')->insert([
                'email' => $accountNo . '@curtin.edu.au.com',
                'token' => Hash::make($newToken),
                'created_at' => new DateTime('NOW')
            ]);

            // Make a mock request to send to the normal reset controller
            $request = new Request([
                'token' => $newToken,
                'accountNo' => $accountNo,
                'password' => $password,
                'password_confirmation' => $password
            ]);
            $this->store($request);
        } else {

            // throw error if there already is
            throw ValidationException::withMessages([
                'email' => 'Please wait before retrying',
            ]);
        }
    }
}
