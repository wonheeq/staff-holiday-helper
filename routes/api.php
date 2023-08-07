<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\BookingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('/accounts', AccountController::class);
Route::get('messages/{accountNo}', [MessageController::class, 'getMessages']);
Route::get('applications/{accountNo}', [ApplicationController::class, 'getApplications']);
Route::get('calendar/{accountNo}', [CalendarController::class, 'getCalendarData']);
Route::get('getBookingOptions/{accountNo}', [BookingController::class, 'getBookingOptions']);
Route::get('getRolesForNominations/{accountNo}', [BookingController::class, 'getRolesForNominations']);
Route::get('getSubstitutionsForUser/{accountNo}', [BookingController::class, 'getSubstitutionsForUser']);

Route::post('createApplication', [ApplicationController::class, 'createApplication']);
Route::get('cancelApplication/{accountNo}/{applicationNo}', [ApplicationController::class, 'cancelApplication']);