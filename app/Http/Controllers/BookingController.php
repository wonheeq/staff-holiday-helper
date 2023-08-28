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
    Returns the nominations for an application for each role that the account has been assigned to, formatted.
    */
    public function getNominationsForApplication(Request $request, String $accountNo, int $applicationNo) {
        // Check if user exists for given accountNo
        if (!Account::where('accountNo', $accountNo)->first()) {
            // User does not exist, return exception
            return response()->json(['error' => 'Account does not exist.'], 500);
        }

        // Check if application exists
        $application = Application::where('applicationNo', $applicationNo)->first();
        if ($application == null) {
            // Application does not exist, return exception
            return response()->json(['error' => 'Application does not exist.'], 500);
        }

        // Check if application belongs to user
        if ($application->accountNo != $accountNo) {
            // Application does not belong to user, return exception
            return response()->json(['error' => 'Application does not belong to user.'], 500);
        }

        $result = array();

        // Get all Nominations associated with the applicationNo
        $nominations = Nomination::where('applicationNo', $applicationNo)->get();
        
        $accountRoleIdsNominated = array();

        // Iterate through each Nomination, extract the AccountRoleId
        // Get RoleId from AccountRole
        // Call RoleController->getRoleFromAccountRoleId() to get the role name
        foreach ($nominations as $nomination) {
            $accountRoleId = $nomination['accountRoleId'];
            $accountRole = AccountRole::where('accountRoleId', $accountRoleId)->first();
            $roleId = $accountRole->roleId;
            $roleName = app(RoleController::class)->getRoleFromAccountRoleId($roleId);

            $nomineeNo = $nomination->nomineeNo;
            $nomination = "";
            // check if nomineeNo == accountNo
            if ($nomineeNo == $accountNo) {
                // Self Nomination
                $nomination = "Self Nomination";
            }
            else {
                // Get name of nominee
                $nominee = Account::where('accountNo', $nomineeNo)->first();
                $nomination = "({$nomineeNo}) {$nominee['fName']} {$nominee['lName']}";
            }

            array_push($accountRoleIdsNominated, $accountRoleId);

            // format and push data to result
            array_push($result, [
                'accountRoleId' => $accountRoleId,
                'selected' => false,
                'role' => $roleName,
                'nomination' => $nomination,
                'visible' => true,
            ]);
        }

        // Add in any new roles that were assigned to the user after they made the application
        // Get all AccountRoles associated with the accountNo
        $accountRoles = AccountRole::where('accountNo', $accountNo)->get();
        
        // Iterate through each AccountRole, extract the roleId
        // Call RoleController->getRoleFromAccountRoleId() to get the role name
        foreach ($accountRoles as $accountRole) {
            if (in_array($accountRole['accountRoleId'], $accountRoleIdsNominated)) {
                // Skip to next if already in results array
                continue;
            }

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

        // Sort data
        usort($data, function ($sub1, $sub2) {
            return $sub1['sDate'] <=> $sub2['sDate'];
        });

        return response()->json($data);
    }
}
