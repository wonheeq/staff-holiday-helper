<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\NominationController;
use App\Http\Controllers\AccountController;
use Illuminate\Support\Facades\Auth;
use App\Models\Account;
use App\Models\Application as ModelApplication;
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
use App\Models\ApplicationReviewHash;
use App\Models\NewNominationsHash;
use App\Models\EditedNominationsHash;
use App\Models\Nomination;
use App\Models\Message;



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


    Route::get('rejectNewNominations/{hash}/{appNo}', function ($hash, $appNo) {
        $newNomsHash = NewNominationsHash::where('hash', $hash)->first();
        $app = ModelApplication::where('applicationNo', $appNo)->first();

        if( $newNomsHash) {
            // check if message exists
            $message = Message::where('receiverNo', $newNomsHash->accountNo)
            ->where('senderNo', $app->accountNo)
            ->where('subject', "Substitution Request")->first();

            if (!$message) {
                return redirect("/home")->with([
                    'customError' => "Already responded to nomination/s"
                ]);
            }
            $request = new Request([
                'accountNo' => $newNomsHash->accountNo,
                'messageId' => $message->messageId,
                'applicationNo' => $appNo,
            ]);
            app(NominationController::class)->rejectNominations($request);
            return redirect("/home")->with([
                'successMessage' => "Successfully rejected nominations"
            ]);
        }

        return redirect("/home");
    });

    Route::get('acceptNewNominations/{hash}/{appNo}', function ($hash, $appNo) {
        $newNomsHash = NewNominationsHash::where('hash', $hash)->first();
        $app = ModelApplication::where('applicationNo', $appNo)->first();

        if( $newNomsHash) {
            // check if message exists
            $message = Message::where('receiverNo', $newNomsHash->accountNo)
            ->where('senderNo', $app->accountNo)
            ->where('subject', "Substitution Request")->first();

            if (!$message) {
                return redirect("/home")->with([
                    'customError' => "Already responded to nomination/s"
                ]);
            }
            $request = new Request([
                'accountNo' => $newNomsHash->accountNo,
                'messageId' => $message->messageId,
                'applicationNo' => $appNo,
            ]);
            app(NominationController::class)->acceptNominations($request);
            return redirect("/home")->with([
                'successMessage' => "Successfully accepted nominations"
            ]);
        }

        return redirect("/home");
    });

    Route::get('acceptEditedNominations/{hash}/{appNo}', function ($hash, $appNo) {
        $editedNomsHash = EditedNominationsHash::where('hash', $hash)->first();
        $app = ModelApplication::where('applicationNo', $appNo)->first();

        if( $editedNomsHash) {
            // check if message exists
            $message = Message::where('receiverNo', $editedNomsHash->accountNo)
            ->where('senderNo', $app->accountNo)
            ->where('subject', "Substitution Request")->first();

            if (!$message) {
                return redirect("/home")->with([
                    'customError' => "Already responded to nomination/s"
                ]);
            }
            $request = new Request([
                'accountNo' => $editedNomsHash->accountNo,
                'messageId' => $message->messageId,
                'applicationNo' => $appNo,
            ]);
            app(NominationController::class)->acceptNominations($request);
            return redirect("/home")->with([
                'successMessage' => "Successfully accepted nominations"
            ]);
        }

        return redirect("/home");
    });

    Route::get('rejectEditedNominations/{hash}/{appNo}', function ($hash, $appNo) {
        $editedNomsHash = EditedNominationsHash::where('hash', $hash)->first();
        $app = ModelApplication::where('applicationNo', $appNo)->first();

        if( $editedNomsHash) {
            // check if message exists
            $message = Message::where('receiverNo', $editedNomsHash->accountNo)
            ->where('senderNo', $app->accountNo)
            ->where('subject', "Substitution Request")->first();

            if (!$message) {
                return redirect("/home")->with([
                    'customError' => "Already responded to nomination/s"
                ]);
            }
            $request = new Request([
                'accountNo' => $editedNomsHash->accountNo,
                'messageId' => $message->messageId,
                'applicationNo' => $appNo,
            ]);
            app(NominationController::class)->rejectNominations($request);
            return redirect("/home")->with([
                'successMessage' => "Successfully rejected nominations"
            ]);
        }

        return redirect("/home");
    });

    Route::get('reviewNominations/{appNo}', function ($appNo) {
        $app = ModelApplication::where('applicationNo', $appNo)->first();
        $creator = Account::where('accountNo', $app->accountNo)->first();
        $user = Auth::user();
        $noms = Nomination::where('applicationNo', $appNo)
        ->where('nomineeNo', $user->accountNo)->get();

        if( count($noms) > 0 )
        {
            return redirect("/home")->with([
                'nomsToReview' => $appNo,
            ]);
        }
        else
        {
            return redirect("/home")->with([
                'customError' => 'Not authorised to review these nominations'
            ]);

        }
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

    Route::get('reviewApplication/{appNo}', function ($appNo) {
        $app = ModelApplication::where('applicationNo', $appNo)->first();
        $creator = Account::where('accountNo', $app->accountNo)->first();
        $lineManager = app(AccountController::class)->getCurrentLineManager($creator->accountNo);
        $user = Auth::user();
        if( $user->accountNo == $lineManager->accountNo)
        {
            return redirect("/home")->with([
                'appToReview' => $appNo,
            ]);
        }
        else
        {
            return redirect("/home")->with([
                'customError' => 'Not authorised to review this application'
            ]);

        }
    });


    Route::get('acceptApplication/{hash}/{appNo}', function ($hash, $appNo) {
        $appReviewHash = ApplicationReviewHash::where('hash', $hash)->first();
        if( $appReviewHash) {
            $request = new Request([
                'accountNo' => $appReviewHash->accountNo,
                'applicationNo' => $appNo,
            ]);
            app(ApplicationController::class)->acceptApplication($request);
            return redirect("/home")->with([
                'successMessage' => "Successfully approved application"
            ]);
        }

        return redirect("/home");
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
