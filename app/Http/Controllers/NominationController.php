<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Nomination;
use App\Models\Application;
use App\Models\AccountRole;
use App\Models\Account;
use App\Models\Role;
use Illuminate\Support\Facades\Log;
class NominationController extends Controller
{
    /*
    Returns all nominations, formatted, for the given application number
    */
    public function getNominations($appNo)
    {
        $nominations = Nomination::where('applicationNo', $appNo)->get();
        $users = [];

        // iteration through each nomination
        foreach ($nominations as $nomination) {
            // get nominee id from nomination
            $nomineeNo = $nomination['nomineeNo'];

            // get name of nominee from user controller
            $nominee_user = app(UserController::class)->getUser($nomineeNo);
            $name = "{$nominee_user['fName']} {$nominee_user['lName']}";

            // Get name of role associated with the account role
            $task = app(RoleController::class)->getRoleFromAccountRoleId($nomination['accountRoleId']);

            array_push($users, array(
                "name" => $name,
                "nomineeNo" => $nomineeNo,
                "task" => $task,
                "status" => $nomination['status'],
            ));
        }

        return $users;
    }




    /*
    Sets the nomination status to rejected
    */
    public function rejectNominations(Request $request) {
        $data = $request->all();
        $accountNo = $data['accountNo'];
        $applicationNo = $data['applicationNo'];

        $account = Account::where('accountNo', $accountNo)->first();
        $application = Application::where('applicationNo', $applicationNo)->first();

        // Check if user exists for given user id
        if ($account == null) {
            // User does not exist, return exception
            return response()->json(['error' => 'Account does not exist.'], 500);
        }

        // Check if application exists for given application No
        if ($application == null) {
            return response()->json(['error' => 'Application does not exist.'], 500);
        }

        // Check if user is nominated for that application
        $nominations = Nomination::where('applicationNo', $applicationNo, "and")
                                    ->where('nomineeNo', $accountNo)->get();

        if (count($nominations) == 0) {
            return response()->json(['error' => 'Account not nominated for application.'], 500);
        }

        // set nomination statues to 'N'
        foreach ($nominations as $nomination) {
            Nomination::where('applicationNo', $nomination->applicationNo, "and")
                        ->where('nomineeNo', $nomination->nomineeNo, "and")
                        ->where('accountRoleId', $nomination->accountRoleId)
                        ->update([
                            "status" => 'N'
                        ]);
        }

        // TODO: Send message to applicant informing them that a nominee declined

        // Set application status to denied by system
        $application->status = 'N';
        $application->processedBy = null;
        $application->save();

        return response()->json(['success'], 200);
    }
}
