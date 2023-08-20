<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\NominationController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\MajorController;
use App\Http\Controllers\SchoolController;



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
Route::post('acknowledgeMessage', [MessageController::class, 'acknowledgeMessage']);

Route::get('/schools', SchoolController::class);
Route::get('/courses', CourseController::class);
Route::get('/majors', MajorController::class);
Route::get('/units', UnitController::class);

Route::get('applications/{accountNo}', [ApplicationController::class, 'getApplications']);
Route::get('calendar/{accountNo}', [CalendarController::class, 'getCalendarData']);
Route::get('getBookingOptions/{accountNo}', [BookingController::class, 'getBookingOptions']);
Route::get('getRolesForNominations/{accountNo}', [BookingController::class, 'getRolesForNominations']);
Route::get('getNominationsForApplication/{accountNo}/{applicationNo}', [BookingController::class, 'getNominationsForApplication']);
Route::get('getSubstitutionsForUser/{accountNo}', [BookingController::class, 'getSubstitutionsForUser']);

Route::post('rejectNominations', [NominationController::class, 'rejectNominations']);
Route::post('acceptSomeNominations', [NominationController::class, 'acceptSomeNominations']);
Route::post('acceptNominations', [NominationController::class, 'acceptNominations']);
Route::post('getRolesForNominee', [NominationController::class, 'getRolesForNominee']);

Route::get('getApplicationForReview/{accountNo}/{applicationNo}', [ApplicationController::class, 'getApplicationForReview']);
Route::post('createApplication', [ApplicationController::class, 'createApplication']);
Route::post('editApplication', [ApplicationController::class, 'editApplication']);
Route::get('cancelApplication/{accountNo}/{applicationNo}', [ApplicationController::class, 'cancelApplication']);
Route::post('acceptApplication', [ApplicationController::class, 'acceptApplication']);
Route::post('rejectApplication', [ApplicationController::class, 'rejectApplication']);

Route::get('getWelcomeMessageData/{accountNo}', [AccountController::class, 'getWelcomeMessageData']);
