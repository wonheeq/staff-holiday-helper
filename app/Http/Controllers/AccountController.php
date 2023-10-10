<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Application;
use Illuminate\Http\Request;
use App\Models\School;
use App\Models\ManagerNomination;
use DateTime;
use DateTimeZone;
use DB;

use Illuminate\Support\Facades\Log;
use Exception;

define("DEFAULT_ADMIN_ACCOUNT_NO", "000002L");

class AccountController extends Controller
{
    /**
     * Handle the incoming request.
     */
    /*
    Returns all Accounts
     */
    public function getAllAccounts(Request $request, String $accountNo)
    {
        // Check if user exists for given accountNo
        if (!Account::where('accountNo', $accountNo)->first()) {
            // User does not exist, return exception
            return response()->json(['error' => 'Account does not exist.'], 500);
        } else {
            $Accounts = Account::get();
            return response()->json($Accounts);
        }
    }

    /*
    Returns the data for the welcome message
    */
    public function getWelcomeMessageData(Request $request, String $accountNo)
    {
        $account = Account::where('accountNo', $accountNo)->first();
        // Check if user exists for given user id
        if (!$account) {
            // User does not exist, return exception
            return response()->json(['error' => 'Account does not exist.'], 500);
        }

        $lineManager = $this->getCurrentLineManager($accountNo);
        
        $data = [
            'name' => "{$account->fName}",
            'lineManager' => [
                'name' => "{$lineManager->fName} {$lineManager->lName}",
                'id' => "{$lineManager->accountNo}"
            ]
        ];     

        return response()->json($data);
    }

    /*
    Return the default admin account for the system
    AKA the super admin - "Admin in charge of admins"
    */
    public function getDefaultAdmin()
    {
        $defaultAdmin = Account::where('accountNo', DEFAULT_ADMIN_ACCOUNT_NO)->first();
        
        if ($defaultAdmin == NULL) { // Default accountNo must have been edited, find another super account
            $defaultAdmin = Account::where('schoolId', 1)->first();
        }

        return $defaultAdmin;
    }

    /*
    Returns the account data for the admin account of the provided schoolId
    */
    public function getAdminForSchool($schoolId)
    {
        if (School::where('schoolId', $schoolId)->first()) {
            // Return FIRST administrator for the given SchoolID WHERE they do not have a superior
            $account = Account::where('schoolId', $schoolId)
            ->where('accountType', "sysadmin")
            ->where('superiorNo', null)
            ->first();
            return $account;
        }

        // School doesn't exist??? return super admin
        return $this->getDefaultAdmin();
    }

    /*
    Returns the current line manager for the account
        If the line manager is on leave, returns the interim line manager
        If the line manager does not exist, returns the default admin
    */
    public function getCurrentLineManager(String $accountNo)
    {
        $user = Account::where('accountNo', $accountNo)->first();
        $superiorNo = $user->superiorNo;

        // Return the super admin
        if ($superiorNo == null) {
            return $this->getDefaultAdmin();
        }

        // Get assigned line manager
        $assignedLineManager = Account::where('accountNo', $superiorNo)->first();
        //Log::debug("Assigned: {$assignedLineManager->accountNo}");
        // Check if assigned Line manager is on leave
        if ($this->isAccountOnLeave($superiorNo)) {
            //Log::debug("Assigned is on leave");
            // Return substitute line manager
            // Get all approved applications of superior
            $applications = Application::where('accountNo', $superiorNo, "and")
                ->where('status', 'Y')->get();

            // Iterate through each application and check if current date is inside the period
            $currentDate = new DateTime();
            $currentDate->setTimezone(new DateTimeZone("Australia/Perth"));
            foreach ($applications as $app) {
                $startDate = new DateTime($app['sDate']);
                $endDate = new DateTime($app['eDate']);

                // Return true if startDate >= currentDate <= endDate
                if ($currentDate >= $startDate && $currentDate <= $endDate) {
                    // NOTE: Intentional logic:
                    /*
                    If the original line manager is on leave
                    And the substitute line manager is on leave
                    The substitute line manager will be returned anyway
                    Since the substitute line manager SHOULD NOT be allowed to nominate others
                    for a role they are temporarily holding
                    */
                    // check that the manager nomination actually exists
                    $managerSub = ManagerNomination::where('applicationNo', $app->applicationNo)
                    ->where('subordinateNo', $accountNo)
                    ->first();
                    if ($managerSub) {
                        $substituteManager = Account::where('accountNo', $managerSub->nomineeNo)->first();
                        if ($substituteManager) {
                            Log::debug("Found Sub: {$substituteManager->accountNo}");
                            return $substituteManager;
                        }
                    }
                }
            }

            // unable to find substitute line manager, return admin for school
            return $this->getAdminForSchool($user->schoolId);
        }

        return $assignedLineManager;
    }

    /*
    Returns true if the account is currently on leave, returns false, otherwise
    */
    public function isAccountOnLeave(String $accountNo)
    {
        $user = Account::where('accountNo', $accountNo)->first();

        // Get all approved applications of user
        $applications = Application::where('accountNo', $accountNo, "and")
            ->where('status', 'Y')->get();
        date_default_timezone_set("Australia/Perth");

        // Iterate through each application and check if current date is inside the period
        $currentDate = new DateTime();
        $currentDate->setTimezone(new DateTimeZone("Australia/Perth"));
        foreach ($applications as $app) {
            $startDate = new DateTime($app['sDate']);
            $endDate = new DateTime($app['eDate']);

            // Return true if startDate >= currentDate <= endDate
            if ($currentDate >= $startDate && $currentDate <= $endDate) {
                return true;
            }
        }
        date_default_timezone_set("UTC");
        return false;
    }

    /*
    Returns all Accounts w/ formatted full names, and a seperate array holding only line managers
    */
    public function getAllAccountsDisplay(Request $request, String $accountNo)
    {  
        // Check if user exists for given accountNo
        if (!Account::where('accountNo', $accountNo)->first()) {
            // User does not exist, return exception
            return response()->json(['error' => 'Account does not exist.'], 500);
        }
        else {
            // Verify that the account is a system admin account
            if (!Account::where('accountNo', $accountNo)->where('accountType', 'sysadmin')->first()) {
                // User is not a system admin, deny access to full table
                return response()->json(['error' => 'User not authorized for request.'], 500);
            }

            $lmAccounts = Account::select("accountNo",DB::raw("CONCAT(fName,' ',lName,' (',accountNo,')') AS fullName"))
                ->where('accountType','!=', 'staff')->get();

            $accounts = Account::select("accountNo",DB::raw("CONCAT(fName,' ',lName,' (',accountNo,')') AS fullName"))->get();

            //Log::info(array($lmAccounts, $accounts));
            //return response()->json($result = array($lmAccounts, $accounts));  
            return response()->json(array($lmAccounts, $accounts));  
        }  
    }
}
