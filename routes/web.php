<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\LoginController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\Request;


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
})->name('login');

Route::get('/reset', function () {
    return Inertia::render('Reset', []);
});

Route::middleware('auth:sanctum')->get('/home', function () {
    return Inertia::render('Home', []);
});


Route::middleware('auth:sanctum')->get('/bookings', function () {
    return Inertia::render('Bookings', [
        'activeScreen' => 'apps'
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

Route::middleware('auth:sanctum')->get('/admin', function () {
    return Inertia::render('Administration', [
        'activeScreen' => 'apps'
    ]);
});

Route::middleware('auth:sanctum')->get('/send-email', [EmailController::class, 'sendEmail']);


// ----------------------AUTHENTICATION RELATED ROUTES-------------------------

Route::post(
    '/login',
    [AuthController::class, 'authenticate']
);

Route::post(
    '/logout',
    [AuthController::class, 'logout']
);

Route::get(
    '/login/create',
    [AuthController::class, 'create']
);

// Route::get('/forgot-password', function () {
//     return Inertia::render('Reset', []);
// })->name('password.reqeust');

Route::post(
    '/reset-password',
    [AuthController::class, 'reset']
)->middleware('guest')->name('password.email');

// Route::get('/reset-password/{token}', function (string $token) {
//     return view('auth.reset-password', ['token' => $token]);
// })->middleware('guest')->name('password.reset');

Route::get('/reset-password/{token}', function (string $token) {
    return Inertia::render('Reset', []);
})->middleware('guest')->name('password.reset');

/*
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
*/
