<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AccountRoleController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\NominationController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\MajorController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\ForeignKeyController;
use App\Http\Controllers\DatabaseController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\EmailPreferenceController;

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


// System Administrator Route Group
Route::middleware(['auth:sanctum', 'sysadmin', 'api'])->group(function () {
    Route::get('/allAccounts/{accountNo}', [AccountController::class, 'getAllAccounts']);
    Route::get('/allApplications/{accountNo}', [ApplicationController::class, 'getAllApplications']);
    Route::get('/allNominations/{accountNo}', [NominationController::class, 'getAllNominations']);
    Route::get('/allMessages/{accountNo}', [MessageController::class, 'getAllMessages']);
    Route::get('/allAccountRoles/{accountNo}', [AccountRoleController::class, 'getAllAccountRoles']);
    Route::get('/allRoles/{accountNo}', [RoleController::class, 'getAllRoles']);
    Route::get('/allUnits/{accountNo}', [UnitController::class, 'getAllUnits']);
    Route::get('/allMajors/{accountNo}', [MajorController::class, 'getAllMajors']);
    Route::get('/allCourses/{accountNo}', [CourseController::class, 'getAllCourses']);
    Route::get('/allSchools/{accountNo}', [SchoolController::class, 'getAllSchools']);
    Route::get('/allFKData/{accountNo}', [ForeignKeyController::class, 'getAllFKs']);
    Route::get('/allAccountsDisplay/{accountNo}', [AccountController::class, 'getAllAccountsDisplay']);
    Route::post('addSingleEntry/{accountNo}', [DatabaseController::class, 'addEntry']);
    Route::post('createSystemNotification', [MessageController::class, 'createSystemNotification']);
    Route::post('setReminderTimeframe', [AdminController::class, 'setReminderTimeframe']);

    Route::get('getCSVTemplate/{accountNo}/{fileName}', [DatabaseController::class, 'sendCSVTemplate']);
    Route::post('addEntriesFromCSV/{accountNo}', [DatabaseController::class, 'addEntriesFromCSV']);
    Route::post('dropEntry/{accountNo}', [DatabaseController::class, 'dropEntry']);
    Route::post('editEntry/{accountNo}', [DatabaseController::class, 'editEntry']);
});


// Line Manager Route Group
Route::middleware(['auth:sanctum', 'lmanager', 'api'])->group(function () {
    Route::get('getStaffMembers/{superiorNo}', [ManagerController::class, 'getStaffMembers']);
    Route::get('getRolesForStaffs/{accountNo}', [ManagerController::class, 'getRolesForStaffs']);
    Route::get('managerApplications/{accountNo}', [ManagerController::class, 'getManagerApplications']);
    Route::post('acceptApplication', [ApplicationController::class, 'acceptApplication']);
    Route::post('rejectApplication', [ApplicationController::class, 'rejectApplication']);
    Route::get('getSpecificStaffMember/{accountNo}', [ManagerController::class, 'getSpecificStaffMember']);
    Route::get('getUCM', [ManagerController::class, 'getUCM']);
    Route::post('addStaffRole', [ManagerController::class, 'addStaffRole']);
    Route::post('removeStaffRole', [ManagerController::class, 'removeStaffRole']);
});


// General Auth Route Group
Route::middleware(['auth:sanctum', 'api'])->group(function () {

    Route::get('messages/{accountNo}', [MessageController::class, 'getMessages']);
    Route::post('acknowledgeMessage', [MessageController::class, 'acknowledgeMessage']);
    Route::get('calendar/{accountNo}', [CalendarController::class, 'getCalendarData']);
    Route::get('getBookingOptions/{accountNo}', [BookingController::class, 'getBookingOptions']);
    Route::get('getRolesForNominations/{accountNo}', [BookingController::class, 'getRolesForNominations']);
    Route::get('getNominationsForApplication/{accountNo}/{applicationNo}', [BookingController::class, 'getNominationsForApplication']);
    Route::get('getSubstitutionsForUser/{accountNo}', [BookingController::class, 'getSubstitutionsForUser']);
    Route::post('rejectNominations', [NominationController::class, 'rejectNominations']);
    Route::post('acceptSomeNominations', [NominationController::class, 'acceptSomeNominations']);
    Route::post('acceptNominations', [NominationController::class, 'acceptNominations']);
    Route::post('getRolesForNominee', [NominationController::class, 'getRolesForNominee']);
    Route::post('createApplication', [ApplicationController::class, 'createApplication']);
    Route::post('editApplication', [ApplicationController::class, 'editApplication']);
    Route::get('cancelApplication/{accountNo}/{applicationNo}', [ApplicationController::class, 'cancelApplication']);
    Route::get('applications/{accountNo}', [ApplicationController::class, 'getApplications']);
    Route::get('getApplicationForReview/{accountNo}/{applicationNo}', [ApplicationController::class, 'getApplicationForReview']);
    Route::get('getWelcomeMessageData/{accountNo}', [AccountController::class, 'getWelcomeMessageData']);
    Route::get('getReminderTimeframe/{accountNo}', [AdminController::class, 'getReminderTimeframe']);
    Route::post('setEmailPreference', [EmailPreferenceController::class, 'setPreference']);
    Route::post('getEmailFrequency', [EmailPreferenceController::class, 'getPreference']);
});


Route::post('getUnitDetails', [UnitController::class, 'getUnitDetails']);
