<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Nomination;
use App\Models\Application;
use App\Models\AccountRole;
use App\Models\Message;
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
    Returns all nominations
     */
    public function getAllNominations(Request $request, String $accountNo)
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

            $nominations = Nomination::get();
            return response()->json($nominations);  
        }  
    }


    /*
    Sets the nomination status to rejected
    */
    public function rejectNominations(Request $request) {
        $data = $request->all();
        $messageId = $data['messageId'];
        $accountNo = $data['accountNo'];
        $applicationNo = $data['applicationNo'];

        $account = Account::where('accountNo', $accountNo)->first();
        $application = Application::where('applicationNo', $applicationNo)->first();
        $message = Message::where('messageId', $messageId)->first();

        // Check if user exists for given user id
        if ($account == null) {
            // User does not exist, return exception
            return response()->json(['error' => 'Account does not exist.'], 500);
        }

        // Check if messaage exists
        if ($message == null) {
            return response()->json(['error' => 'Message does not exist.'], 500);
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

        // update message
        $message->acknowledged = true;
        $message->save();

        $rejectedRoles = [];

        // set nomination statues to 'N' and add Role to rejectedRoles
        foreach ($nominations as $nomination) {
            Nomination::where('applicationNo', $nomination->applicationNo, "and")
            ->where('nomineeNo', $nomination->nomineeNo, "and")
            ->where('accountRoleId', $nomination->accountRoleId)
            ->update([
                "status" => 'N'
            ]);

            array_push(
                $rejectedRoles,
                app(RoleController::class)->getRoleFromAccountRoleId($nomination->accountRoleId)
            );
        }

        // Send message to applicant informing them that a nominee declined a nomination
        app(MessageController::class)->notifyApplicantNominationDeclined($applicationNo, $accountNo, $rejectedRoles);

        // Set application status to denied by system
        $application->status = 'N';
        $application->processedBy = null;
        $application->save();

        return response()->json(['success'], 200);
    }

    /*
    Sets the nomination status to acceped
    */
    public function acceptNominations(Request $request) {
        $data = $request->all();
        $messageId = $data['messageId'];
        $accountNo = $data['accountNo'];
        $applicationNo = $data['applicationNo'];

        $account = Account::where('accountNo', $accountNo)->first();
        $application = Application::where('applicationNo', $applicationNo)->first();
        $message = Message::where('messageId', $messageId)->first();

        // Check if user exists for given user id
        if ($account == null) {
            // User does not exist, return exception
            return response()->json(['error' => 'Account does not exist.'], 500);
        }

        // Check if messaage exists
        if ($message == null) {
            return response()->json(['error' => 'Message does not exist.'], 500);
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

        // update message
        $message->acknowledged = true;
        $message->save();

        // set nomination statues to 'Y'
        foreach ($nominations as $nomination) {
            Nomination::where('applicationNo', $nomination->applicationNo, "and")
            ->where('nomineeNo', $nomination->nomineeNo, "and")
            ->where('accountRoleId', $nomination->accountRoleId)
            ->update([
                "status" => 'Y'
            ]);
        }

        
        // Set application status to Undecided by system if all nominees agreed
        $allNominations = Nomination::where('applicationNo', $applicationNo)->get()->toArray();
        $acceptedNominations = Nomination::where('applicationNo', $applicationNo, 'and')
                                            ->where('status', 'Y')->get()->toArray();
        if (count($acceptedNominations) == count($allNominations)) {
            $application->status = 'U';
            $application->processedBy = null;
            $application->save();

            // Get current line manager account number
            $superiorNo = app(AccountController::class)->getCurrentLineManager($accountNo)->accountNo;
            // Notify line manager of new application to review
            app(MessageController::class)->notifyManagerApplicationAwaitingReview($superiorNo, $applicationNo);
        }

        return response()->json(['success'], 200);
    }

    /*
    Sets nomination status to 'Y' or 'N' depending on input
    */
    public function acceptSomeNominations(Request $request) {
        $data = $request->all();
        $messageId = $data['messageId'];
        $accountNo = $data['accountNo'];
        $applicationNo = $data['applicationNo'];
        $responseData = $data['responses'];

        $account = Account::where('accountNo', $accountNo)->first();
        $application = Application::where('applicationNo', $applicationNo)->first();
        $message = Message::where('messageId', $messageId)->first();

        // Check if user exists for given user id
        if ($account == null) {
            // User does not exist, return exception
            return response()->json(['error' => 'Account does not exist.'], 500);
        }

        // Check if messaage exists
        if ($message == null) {
            return response()->json(['error' => 'Message does not exist.'], 500);
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


        // Check if responses is not null
        if ($responseData == null) {
            return response()->json(['error' => 'Responses are null.'], 500);
        }

        // Check if all responses are != 'U'
        foreach ($responseData as $response) {
            $status = $response['status'];
            if ($status == 'U') {
                return response()->json(['error' => 'Invalid response to nomination.'], 500);
            }
        }

        // update message
        $message->acknowledged = true;
        $message->save();

        $rejectedRoles = [];
        // set nomination statues to 'Y' or 'N'
        foreach ($responseData as $response) {
            $accountRoleId = $response['accountRoleId'];
            $status = $response['status'];
            Nomination::where('applicationNo', $applicationNo, "and")
            ->where('nomineeNo', $accountNo, "and")
            ->where('accountRoleId', $accountRoleId)
            ->update([
                "status" => $status
            ]);

            // Add to rejectedRoles if status is N
            if ($status == 'N') {
                array_push(
                    $rejectedRoles,
                    app(RoleController::class)->getRoleFromAccountRoleId($accountRoleId)
                );
            }
        }

        if (count($rejectedRoles) == 0) {
            // Set application status to Undecided by system if all nominees agreed
            $allNominations = Nomination::where('applicationNo', $applicationNo)->get()->toArray();
            $acceptedNominations = Nomination::where('applicationNo', $applicationNo, 'and')
                                                ->where('status', 'Y')->get()->toArray();
            if (count($acceptedNominations) == count($allNominations)) {
                $application->status = 'U';
                $application->processedBy = null;
                $application->save();

                // Get current line manager account number
                $superiorNo = app(AccountController::class)->getCurrentLineManager($accountNo)->accountNo;
                // Notify line manager of new application to review
                app(MessageController::class)->notifyManagerApplicationAwaitingReview($superiorNo, $applicationNo);
            }
        }
        else {
            // Send message to applicant informing them that a nominee declined a nomination
            app(MessageController::class)->notifyApplicantNominationDeclined($applicationNo, $accountNo, $rejectedRoles);

            // Set application status to denied by system
            $application->status = 'N';
            $application->processedBy = null;
            $application->save();
        }
        

        return response()->json(['success'], 200);
    }


    /*
    Gets the roles that an account has been nominated for
    */
    public function getRolesForNominee(Request $request) {
        $data = $request->all();
        $nomineeNo = $data['accountNo'];
        $applicationNo = $data['applicationNo'];

        $account = Account::where('accountNo', $nomineeNo)->first();
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
                                    ->where('nomineeNo', $nomineeNo)->get();

        if (count($nominations) == 0) {
            return response()->json(['error' => 'Account not nominated for application.'], 500);
        }


        $result = array();
        // Iterate through the nominations, get role name from accountRoleId
        foreach ($nominations as $nomination) {
            $accountRoleId = $nomination->accountRoleId;

            array_push(
                $result,
                [
                    "roleName" => app(RoleController::class)->getRoleFromAccountRoleId($accountRoleId),
                    "accountRoleId" => $accountRoleId,
                    "status" => 'U',
                ]
            );
        }

        return response()->json($result);
    }
}
