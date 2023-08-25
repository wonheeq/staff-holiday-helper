<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PasswordResetController extends Controller
{
    // Request a password reset
    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // send link
        $status = Password::sendResetLink(
            $request->only('accountNo')
        );

        // check if link sent
        if ($status == Password::RESET_LINK_SENT) {
            return response()->json([
                'status' => __($status),
            ]);
        }

        // exception if link is not sent
        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }


    /**
     * Display the password reset view.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('Reset', [
            'email' => $request->email,
            'token' => $request->route('token'),
        ]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'accountNo' => 'required',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        //attempt password reset
        $status = Password::reset(
            $request->only('accountNo', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    // 'remember_token' => Str::random(60),
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


    /**
     * Handle an incoming new password request from the HOME page
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function homeStore(Request $request)
    {
        //dd($request);
        // validate the request
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

        // Manually generate a new token (normally done by the method that sends the
        // password reset email)
        $newToken = app('auth.password.broker')->createToken($user);

        // Make a mock request to send to the normal reset controller
        $request = new Request([
            'token' => $newToken,
            'accountNo' => $accountNo,
            'password' => $password,
            'password_confirmation' => $password
        ]);
        $this->store($request);
    }
}
