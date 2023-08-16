<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Application;
use Illuminate\Http\Request;
use \DateTime;

define("DEFAULT_ADMIN_ACCOUNT_NO", "000002L");

class AccountController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {       
        //$accounts = Account::get();
        //return response()->json($accounts);

        return Account::all(); 
    }

    /*
    Returns the account data for the default admin account
    */
    public function getDefaultAdmin() {
        return Account::where('accountNo', DEFAULT_ADMIN_ACCOUNT_NO)->first();
    }

    /*
    Returns the current line manager for the account
        If the line manager is on leave, returns the interim line manager
        If the line manager does not exist, returns the default admin
    */
    public function getCurrentLineManager(String $accountNo) {
        try {
            // Attempt to get user
            $user = Account::where('accountNo', $accountNo)->first();
            $superiorNo = $user->superiorNo;

            if ($superiorNo == null) {
                throw new ErrorException();
            }
            
            // Get assigned line manager
            $assignedLineManager = Account::where('accountNo', $superiorNo)->first();
            // Check if assigned Line manager is on leave
            if ($this->isAccountOnLeave($superiorNo)) {
                return $this->getDefaultAdmin();
            }

            return $assignedLineManager;
        }
        catch (Exception $e) {
            // Error occurred - return default admin user
            return $this->getDefaultAdmin();
        }
    }

    /*
    Returns true if the account is currently on leave, returns false, otherwise
    */
    public function isAccountOnLeave(String $accountNo) {
        try {
            // Attempt to get user
            $user = Account::where('accountNo', $accountNo)->first();
            
            // Get all approved applications of user
            $applications = Application::where('accountNo', $accountNo, "and")
                ->where('status', 'Y')->get();

            // Iterate through each application and check if current date is inside the period
            $currentDate = new DateTime();
            foreach ($applications as $app) {
                $startDate = new DateTime($app['sDate']);
                $endDate = new DateTime($app['eDate']);

                // Return true if startDate >= currentDate <= endDate
                if ($currentDate >= $startDate && $currentDate <= $endDate) {
                    return true;
                }
            }
        }
        catch (Exception $e) {
            // Error occurred, return default response
            return false;
        }
    }
}
