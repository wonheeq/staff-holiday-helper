<?php

use App\Http\Controllers\EmailController;
use App\Http\Controllers\LoginController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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

Route::post(
    '/login',
    [LoginController::class, 'authenticate']
);

Route::get('/testloginone', function () {
    return Inertia::render('Auth/Login', []);
});

Route::get('/testloginone', function () {
    return Inertia::render('Auth/Login', []);
});

Route::get('/testlogintwo', function () {
    return Inertia::render('Temppage', []);
});


Route::get('/', function () {
    return Inertia::render('Landing', []);
    /*
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
    */
});

Route::get('/reset', function () {
    return Inertia::render('Reset', []);
});

Route::get('/home', function () {
    return Inertia::render('Home', []);
});


Route::get('/bookings', function () {
    return Inertia::render('Bookings', [
        'activeScreen' => 'apps'
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

Route::get('/admin', function () {
    return Inertia::render('Administration', [
        'activeScreen' => 'apps'
    ]);
});

Route::get('/send-email', [EmailController::class, 'sendEmail']);

/*
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
*/

/*
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
*/
