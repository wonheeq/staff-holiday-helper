<?php

use App\Http\Controllers\EmailController;
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


Route::get('/bookings/{screenProp?}', function (string $screenProp = "apps") {
    return Inertia::render('Bookings', [
        'screenProp' => $screenProp
    ]);
});

Route::get('/admin/{screenProp?}', function (string $screenProp = "viewData") {
    return Inertia::render('Administration', [
        'screenProp' => $screenProp
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
