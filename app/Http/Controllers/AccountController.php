<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Application;
use Illuminate\Http\Request;
use \DateTime;
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
    Returns the account data for the default admin account
    */
    public function getDefaultAdmin()
    {
        return Account::where('accountNo', DEFAULT_ADMIN_ACCOUNT_NO)->first();
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

        if ($superiorNo == null) {
            return $this->getDefaultAdmin();
        }

        // Get assigned line manager
        $assignedLineManager = Account::where('accountNo', $superiorNo)->first();
        // Check if assigned Line manager is on leave
        if ($this->isAccountOnLeave($superiorNo)) {

            // return default for now
            // TODO: return substitute line manager
            return $this->getDefaultAdmin();
        }

        return $assignedLineManager;
    }

    /*
    Returns true if the account is currently on leave, returns false, otherwise
    */
    public function isAccountOnLeave(String $accountNo)
    {
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
        } catch (Exception $e) {
            // Error occurred, return default response
            return false;
        }
    }
}
