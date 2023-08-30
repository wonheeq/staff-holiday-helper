<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\LoginController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\Request;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\AuthenticationController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/



Route::get('/', function () {
    return Inertia::render('Landing', []);
});


Route::get('/login', function () {
    return Inertia::render('Landing', []);
});



Route::get('/reset', function () {
    return Inertia::render('Reset', []);
});

Route::middleware('auth:sanctum')->get('/home', function () {
    return Inertia::render('Home', []);
});



Route::middleware('auth:sanctum')->get('/bookings/{screenProp?}', function (string $screenProp = "apps") {
    return Inertia::render('Bookings', [
        'screenProp' => $screenProp
    ]);
});

Route::middleware('auth:sanctum')->get('/bookings/apps', function () {
    return Inertia::render('Bookings', [
        'activeScreen' => 'apps'
    ]);
});

Route::middleware('auth:sanctum')->get('/bookings/create', function () {
    return Inertia::render('Bookings', [
        'activeScreen' => 'create'
    ]);
});

Route::middleware('auth:sanctum')->get('/bookings/subs', function () {
    return Inertia::render('Bookings', [
        'activeScreen' => 'subs'
    ]);
});

Route::middleware('auth:sanctum')->get('/admin/{screenProp?}', function (string $screenProp = "viewData") {
    return Inertia::render('Administration', [
        'screenProp' => $screenProp
    ]);
});

Route::middleware('auth:sanctum')->get('/send-email', [EmailController::class, 'sendEmail']);


// ----------------------AUTHENTICATION RELATED ROUTES------------------------- //

Route::post(
    '/login',
    [AuthenticationController::class, 'login']
)->name('login');


Route::post(
    '/logout',
    [AuthenticationController::class, 'logout']
);


Route::post(
    '/reset-password',
    [AuthenticationController::class, 'reset']
)->name('password.email');


Route::get(
    '/reset-password/{token}',
    [AuthenticationController::class, 'create']
)->name('password.reset');


Route::post(
    '/update-password',
    [AuthenticationController::class, 'store']
)->name('password.store');


Route::post(
    '/change-password',
    [AuthenticationController::class, 'homeStore']
)->name('password.homeStore');
