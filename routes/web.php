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
use App\Http\Middleware\EnsureUserIsManager;
use App\Http\Controllers\MessageController;
use App\Models\WelcomeHash;

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

// Landing Page Route Group
Route::get('/', function () {
    return Inertia::render('Landing', []);
});

Route::get('/login', function () {
    return Inertia::render('Landing', []);
});

Route::get('/reset', function () {
    return Inertia::render('Reset', []);
});

Route::get('/set-password/{hash}', function ($hash) {
    $welcomeHash = WelcomeHash::where('hash', $hash)->first();
    if ($welcomeHash) {
        return Inertia::render('Welcome', []);
    }

    return redirect("/login");


});




// Home Page
Route::middleware(['auth:sanctum', 'web'])->get('/home', function () {
    return Inertia::render('Home', []);
});



// Bookings Page Route Group
Route::middleware(['auth:sanctum', 'web'])->group(function () {
    Route::get('/bookings/{screenProp?}', function (string $screenProp = "apps") {
        return Inertia::render('Bookings', [
            'screenProp' => $screenProp
        ]);
    });

    Route::get('/bookings/apps', function () {
        return Inertia::render('Bookings', [
            'activeScreen' => 'apps'
        ]);
    });

    Route::get('/bookings/create', function () {
        return Inertia::render('Bookings', [
            'activeScreen' => 'create'
        ]);
    });

    Route::get('/bookings/subs', function () {
        return Inertia::render('Bookings', [
            'activeScreen' => 'subs'
        ]);
    });
});



// Line Manager Page Route Group
Route::middleware(['auth:sanctum', 'lmanager', 'web'])->group(function () {

    Route::get('/manager/{screenProp?}', function (string $screenProp = "appRequest") {
        return Inertia::render('Manager', [
            'screenProp' => $screenProp
        ]);
    });

    Route::get('/Manager/appRequest', function () {
        return Inertia::render('Manager', [
            'activeScreen' => 'appRequest'
        ]);
    });

    Route::get('/Manager/manage', function () {
        return Inertia::render('Manager', [
            'activeScreen' => 'manage'
        ]);
    });
});



// Admin Page Route Group
Route::middleware(['auth:sanctum', 'sysadmin', 'web'])->group(function () {
    Route::get('/admin/{screenProp?}', function (string $screenProp = "viewData") {
        return Inertia::render('Administration', [
            'screenProp' => $screenProp
        ]);
    });
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

Route::get(
    '/test',
    // [MessageController::class, 'demoSendDailyMessages']
    [AuthenticationController::class, 'test']
);
