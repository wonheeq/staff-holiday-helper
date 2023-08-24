<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

// // Authentication Routes all users should be able to use
// Route::middleware('guest')->group(function () {
//     Route::post(
//         '/login',
//         [AuthController::class, 'authenticate']
//     )->name('login');

//     Route::post(
//         '/logout',
//         [AuthController::class, 'logout']
//     );

//     Route::get(
//         '/login/create',
//         [AuthController::class, 'create']
//     );

//     Route::post(
//         '/reset-password',
//         [PasswordResetController::class, 'reset']
//     )->name('password.email');

//     Route::get('/reset-password/{token}', [PasswordResetController::class, 'create'])
//         ->name('password.reset');

//     Route::post('/update-password', [PasswordResetController::class, 'store'])
//         ->name('password.store');
// });


// // Uthentication routes only signed-in users should be able to use
// Route::middleware('auth')->group(function () {
//     Route::post('/change-password', [PasswordResetController::class, 'homeStore'])
//         ->name('password.homeStore');
// });
