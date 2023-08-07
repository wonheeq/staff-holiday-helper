<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\AccountRole;
use App\Models\Nomination;
use App\Models\Application;

class BookingController extends Controller
{
    /*
    Returns a list of all account numbers in the system that belong to the same school
    as the account for the given account number.
    This list excludes the given account number.
    This list has "Self Nomination" at the front of the array
    */
    public function getBookingOptions(Request $request, String $accountNo) {
        // Check if user exists for given accountNo
        if (!Account::where('accountNo', $accountNo)->first()) {
            // User does not exist, return exception
            return response()->json(['error' => 'Account does not exist.'], 500);
        }

        // Todo: Check for schoolId after it gets implemented 

        $users = Account::where("accountNo", "!=", $accountNo)->get();
        $data = ["Self Nomination"];

        foreach ($users as $user) {
            array_push($data, "({$user['accountNo']}) {$user['fName']} {$user['lName']}");
        }

        return response()->json($data);
    }

    /*
    Returns a list of roles that the account has been assigned to, formatted.
    */
    public function getRolesForNominations(Request $request, String $accountNo) {
        // Check if user exists for given accountNo
        if (!Account::where('accountNo', $accountNo)->first()) {
            // User does not exist, return exception
            return response()->json(['error' => 'Account does not exist.'], 500);
        }

        $result = array();

        // Get all AccountRoles associated with the accountNo
        $accountRoles = AccountRole::where('accountNo', $accountNo)->get();
        
        // Iterate through each AccountRole, extract the roleId
        // Call RoleController->getRoleFromAccountRoleId() to get the role name
        foreach ($accountRoles as $accountRole) {
            $roleId = $accountRole['roleId'];
            $roleName = app(RoleController::class)->getRoleFromAccountRoleId($roleId);

            // format and push data to result
            array_push($result, [
                'accountRoleId' => $accountRole['accountRoleId'],
                'selected' => false,
                'role' => $roleName,
                'nomination' => "",
                'visible' => true,
            ]);
        }

        return response()->json($result);
    }

    /*
    Returns a list of substitutions the user has agreed to takeover
    */
    public function getSubstitutionsForUser(Request $request, String $accountNo) {
         // Check if user exists for given accountNo
         if (!Account::where('accountNo', $accountNo)->first()) {
            // User does not exist, return exception
            return response()->json(['error' => 'Account does not exist.'], 500);
        }

        // Get all nominations where the user is a nominee
        $nominations = Nomination::where("nomineeNo", "=", $accountNo)->get();

        $data = array();

        // Iterate through each nomination
        foreach ($nominations as $nomination) {
            // Grab application details of accepted nominations
            // Only get substitution data for accepted nominations
            if ($nomination['status'] == 'Y') {
                $application = Application::where("applicationNo", "=", $nomination['applicationNo'])->first();
                
                if ($application != null && $application['status'] == 'Y') {
                    // Get details of accepted application

                    $startDate = $application['sDate'];
                    $endDate = $application['eDate'];
                    $applicationMaker = Account::where("accountNo", "=", $application['accountNo'])->first();
                    $applicationMakerName = "{$applicationMaker['fName']} {$applicationMaker['lName']}";
                    // Call Role Controller to get role descriptor
                    $task = app(RoleController::class)->getRoleFromAccountRoleId($nomination['accountRoleId']);

                    $content = "{$task} for {$applicationMaker} ({$startDate} - {$endDate})";

                    $subData = array(
                        'sDate' => $startDate,
                        'eDate' => $endDate,
                        'task' => $task,
                        'applicantName' => $applicationMakerName,
                    );
                    array_push($data, $subData);
                }
            }
        }

        return response()->json($data);
    }
}
