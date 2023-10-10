<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Nomination;
use App\Models\ManagerNomination;
use App\Models\AccountRole;
use App\Models\Application;
use App\Models\Message;
use App\Models\Account;
use App\Models\Role;
use Illuminate\Support\Facades\Log;

class NominationController extends Controller
{
    /*
    Returns all nominations, formatted, for the given Nomination number
    FOR display on frontend
    */
    public function getNominationsToDisplay($appNo)
    {
        $nominations = Nomination::where('NominationNo', $appNo)->get();
        $managerNominations = ManagerNomination::where('NominationNo', $appNo)->get();
        $result = [];
        $processedNomineeNos = [];
        $processedManagerNomineeNos = [];

        // iteration through each nomination
        foreach ($nominations as $all) {
            // get nominee id from nomination
            $nomineeNo = $all['nomineeNo'];

            if (in_array($nomineeNo, $processedNomineeNos)) { continue; }
            array_push($processedNomineeNos, $nomineeNo);

            // get name of nominee from user controller
            $nominee_user = app(UserController::class)->getUser($nomineeNo);
            $name = "{$nominee_user['fName']} {$nominee_user['lName']}";

            $noms = Nomination::where('NominationNo', $appNo)
            ->where('nomineeNo', $nomineeNo)->get();

            foreach($noms as $n) {
                // Get name of role associated with the account role
                $task = app(RoleController::class)->getRoleFromAccountRoleId($n['accountRoleId']);

                if (!array_key_exists($nomineeNo, $result)) {
                    $result[$nomineeNo] = array(
                        'nomineeName' => $name,
                        'nomineeNo' => $nomineeNo,
                        'tasks' => array()
                    );
                }
                array_push(
                    $result[$nomineeNo]['tasks'],
                    $task
                );
            }
        }

         // iteration through each manager nomination
         foreach ($managerNominations as $nomination) {
            // get nominee id from nomination
            $nomineeNo = $nomination['nomineeNo'];

            if (in_array($nomineeNo, $processedManagerNomineeNos)) { continue; }
            array_push($processedManagerNomineeNos, $nomineeNo);

            // get name of nominee from user controller
            $nominee_user = app(UserController::class)->getUser($nomineeNo);
            $name = "{$nominee_user['fName']} {$nominee_user['lName']}";
            
            $noms = ManagerNomination::where('NominationNo', $appNo)
            ->where('nomineeNo', $nomineeNo)->get();

            foreach($noms as $n) {
                $sub = Account::where('accountNo', $n['subordinateNo'])->first();
                $task = "Line Manager for ({$sub->accountNo}) {$sub->fName} {$sub->lName}";

                if (!array_key_exists($nomineeNo, $result)) {
                    $result[$nomineeNo] = array(
                        'nomineeName' => $name,
                        'nomineeNo' => $nomineeNo,
                        'tasks' => array()
                    );
                }
                array_push(
                    $result[$nomineeNo]['tasks'],
                    $task
                );
            }
        }

        // send without string index
        $finalResult = array();
        foreach ($result as $key => $value) {
            array_push($finalResult, $value);
        }

        return $finalResult;
    }


    /*
    Returns all nominations, formatted, for the given Nomination number
    */
    public function getNominations($appNo)
    {
        $nominations = Nomination::where('NominationNo', $appNo)->get();
        $managerNominations = ManagerNomination::where('NominationNo', $appNo)->get();
        $result = [];

        // iteration through each nomination
        foreach ($nominations as $nomination) {
            // get nominee id from nomination
            $nomineeNo = $nomination['nomineeNo'];

            // get name of nominee from user controller
            $nominee_user = app(UserController::class)->getUser($nomineeNo);
            $name = "{$nominee_user['fName']} {$nominee_user['lName']}";

            // Get name of role associated with the account role
            $task = app(RoleController::class)->getRoleFromAccountRoleId($nomination['accountRoleId']);

            array_push($result, array(
                "name" => $name,
                "nomineeNo" => $nomineeNo,
                "task" => $task,
                "status" => $nomination['status'],
            ));
        }

         // iteration through each manager nomination
         foreach ($managerNominations as $nomination) {
            // get nominee id from nomination
            $nomineeNo = $nomination['nomineeNo'];

            // get name of nominee from user controller
            $nominee_user = app(UserController::class)->getUser($nomineeNo);
            $name = "{$nominee_user['fName']} {$nominee_user['lName']}";

            $sub = Account::where('accountNo', $nomination['subordinateNo'])->first();
            $task = "Line Manager for ({$sub->accountNo}) {$sub->fName} {$sub->lName}";

            array_push($result, array(
                "name" => $name,
                "nomineeNo" => $nomineeNo,
                "task" => $task,
                "status" => $nomination['status'],
            ));
        }

        return $result;
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

        // Super admin can view all nominations.
        if (Account::where('accountNo', $accountNo)->where('schoolId', 1)->exists()) {
            $nominations = Nomination::get();
        }
        else {
            // Get schoolId of user
            $schoolCode = Account::select('schoolId')->where('accountNo', $accountNo)->first();
            //Log::info($schoolCode);
            
            $additionalApplications = Application::join('accounts', 'applications.accountNo', '=', 'accounts.accountNo')
                                                 ->select('applications.applicationNo')
                                                 ->where('schoolId', $schoolCode->schoolId)->get();

            //Log::info($additionalApplications);

            $nominations = Nomination::join('accounts', 'nominations.nomineeNo', '=', 'accounts.accountNo')
                                     ->join('applications', 'nominations.applicationNo', '=', 'applications.applicationNo')
                                     ->select('nominations.*')
                                     ->where('schoolId', $schoolCode->schoolId)
                                     //->where('schoolId', 9) // For testing
                                     ->orWhere(function ($query) use ($additionalApplications) {
                                        $query->whereIn('nominations.applicationNo', $additionalApplications);
                                     })->get();
        }
        
        return response()->json($nominations); 
    }


    /*
    Sets the nomination status to rejected
    */
    public function rejectNominations(Request $request)
    {
        $data = $request->all();
        $messageId = $data['messageId'];
        $accountNo = $data['accountNo'];
        $NominationNo = $data['NominationNo'];

        $account = Account::where('accountNo', $accountNo)->first();
        $Nomination = Nomination::where('NominationNo', $NominationNo)->first();
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

        // Check if Nomination exists for given Nomination No
        if ($Nomination == null) {
            return response()->json(['error' => 'Nomination does not exist.'], 500);
        }

        // Check if user is nominated for that Nomination
        $nominations = Nomination::where('NominationNo', $NominationNo, "and")
            ->where('nomineeNo', $accountNo)->get();
        $managerNominations = ManagerNomination::where('NominationNo', $NominationNo, "and")
        ->where('nomineeNo', $accountNo)->get();

        if (count($nominations) + count($managerNominations) == 0) {
            return response()->json(['error' => 'Account not nominated for Nomination.'], 500);
        }

        // update message
        $message->acknowledged = true;
        $message->save();

        $rejectedRoles = [];

        // set nomination statues to 'N' and add Role to rejectedRoles
        foreach ($nominations as $nomination) {
            Nomination::where('NominationNo', $nomination->NominationNo, "and")
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

        // set manager nomination statues to 'N' and add Role to rejectedRoles
        foreach ($managerNominations as $nomination) {
            ManagerNomination::where('NominationNo', $nomination->NominationNo, "and")
            ->where('nomineeNo', $nomination->nomineeNo, "and")
            ->where('subordinateNo', $nomination->subordinateNo)
            ->update([
                "status" => 'N'
            ]);

            $sub = Account::where('accountNo', $nomination->subordinateNo)->first();

            array_push(
                $rejectedRoles,
                "Line Manager for ({$sub->accountNo}) {$sub->fName} {$sub->lName}"
            );
        } 

        // Send message to applicant informing them that a nominee declined a nomination
        app(MessageController::class)->notifyApplicantNominationDeclined($NominationNo, $accountNo, $rejectedRoles);

        // Set Nomination status to denied by system
        $Nomination->status = 'N';
        $Nomination->processedBy = null;
        $Nomination->rejectReason = "At least one nomination was rejected.";
        $Nomination->save();

        return response()->json(['success'], 200);
    }

    /*
    Sets the nomination status to acceped
    */
    public function acceptNominations(Request $request)
    {
        $data = $request->all();
        $messageId = $data['messageId'];
        $accountNo = $data['accountNo'];
        $NominationNo = $data['NominationNo'];

        $account = Account::where('accountNo', $accountNo)->first();
        $Nomination = Nomination::where('NominationNo', $NominationNo)->first();
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

        // Check if Nomination exists for given Nomination No
        if ($Nomination == null) {
            return response()->json(['error' => 'Nomination does not exist.'], 500);
        }

        // Check if user is nominated for that Nomination
        $nominations = Nomination::where('NominationNo', $NominationNo, "and")
            ->where('nomineeNo', $accountNo)->get();
        $managerNominations = ManagerNomination::where('NominationNo', $NominationNo, "and")
            ->where('nomineeNo', $accountNo)->get();

        if (count($nominations) + count($managerNominations) == 0) {
            return response()->json(['error' => 'Account not nominated for Nomination.'], 500);
        }

        // update message
        $message->acknowledged = true;
        $message->save();

        // set nomination statues to 'Y'
        foreach ($nominations as $nomination) {
            Nomination::where('NominationNo', $nomination->NominationNo, "and")
            ->where('nomineeNo', $nomination->nomineeNo, "and")
            ->where('accountRoleId', $nomination->accountRoleId)
            ->update([
                "status" => 'Y'
            ]);
        }
        // set manager nomination statues to 'Y'
        foreach ($managerNominations as $nomination) {
            ManagerNomination::where('NominationNo', $nomination->NominationNo, "and")
            ->where('nomineeNo', $nomination->nomineeNo, "and")
            ->where('subordinateNo', $nomination->subordinateNo)
            ->update([
                "status" => 'Y'
            ]);
        }


        // Set Nomination status to Undecided by system if all nominees agreed
        $allNominations = Nomination::where('NominationNo', $NominationNo)->get();
        $acceptedNominations = Nomination::where('NominationNo', $NominationNo, 'and')
            ->where('status', 'Y')->get();

        $allManagerNominations = ManagerNomination::where('NominationNo', $NominationNo)->get();
        $acceptedManagerNominations = ManagerNomination::where('NominationNo', $NominationNo, 'and')
            ->where('status', 'Y')->get();
        if (count($acceptedNominations) + count($acceptedManagerNominations) == count($allNominations) + count($allManagerNominations)) {
            $Nomination->status = 'U';
            $Nomination->processedBy = null;
            $Nomination->save();

            // Get current line manager account number
            $superiorNo = app(AccountController::class)->getCurrentLineManager($accountNo)->accountNo;
            // Notify line manager of new Nomination to review
            app(MessageController::class)->notifyManagerNominationAwaitingReview($superiorNo, $NominationNo);
        }

        return response()->json(['success'], 200);
    }

    /*
    Sets nomination status to 'Y' or 'N' depending on input
    */
    public function acceptSomeNominations(Request $request)
    {
        $data = $request->all();
        $messageId = $data['messageId'];
        $accountNo = $data['accountNo'];
        $NominationNo = $data['NominationNo'];
        $responseData = $data['responses'];

        $account = Account::where('accountNo', $accountNo)->first();
        $Nomination = Nomination::where('NominationNo', $NominationNo)->first();
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

        // Check if Nomination exists for given Nomination No
        if ($Nomination == null) {
            return response()->json(['error' => 'Nomination does not exist.'], 500);
        }

        // Check if user is nominated for that Nomination
        $nominations = Nomination::where('NominationNo', $NominationNo, "and")
            ->where('nomineeNo', $accountNo)->get();
        // Check if user is nominated for that Nomination
        $managerNominations = ManagerNomination::where('NominationNo', $NominationNo, "and")
        ->where('nomineeNo', $accountNo)->get();

        if (count($nominations) + count($managerNominations) == 0) {
            return response()->json(['error' => 'Account not nominated for Nomination.'], 500);
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
            if ($accountRoleId != "MANAGER") {
                Nomination::where('NominationNo', $NominationNo, "and")
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
            else {
                ManagerNomination::where('NominationNo', $NominationNo, "and")
                ->where('nomineeNo', $accountNo, "and")
                ->where('subordinateNo', $response['subordinateNo'])
                ->update([
                    "status" => $status
                ]);

                // Add to rejectedRoles if status is N
                if ($status == 'N') {
                    $sub = Account::where('accountNo', $response['subordinateNo'])->first();

                    array_push(
                        $rejectedRoles,
                        "Line Manager for ({$sub->accountNo}) {$sub->fName} {$sub->lName}"
                    );
                }
            }
        }

        if (count($rejectedRoles) == 0) {
            // Set Nomination status to Undecided by system if all nominees agreed
            $allNominations = Nomination::where('NominationNo', $NominationNo)->get();
            $acceptedNominations = Nomination::where('NominationNo', $NominationNo, 'and')
                ->where('status', 'Y')->get();

            $allManagerNominations = ManagerNomination::where('NominationNo', $NominationNo)->get();
            $acceptedManagerNominations = ManagerNomination::where('NominationNo', $NominationNo, 'and')
                ->where('status', 'Y')->get();
            if (count($acceptedNominations) + count($acceptedManagerNominations) == count($allNominations) + count($allManagerNominations)) {
                $Nomination->status = 'U';
                $Nomination->processedBy = null;
                $Nomination->save();

                // Get current line manager account number
                $superiorNo = app(AccountController::class)->getCurrentLineManager($accountNo)->accountNo;
                // Notify line manager of new Nomination to review
                app(MessageController::class)->notifyManagerNominationAwaitingReview($superiorNo, $NominationNo);
            }
        }
        else {
            // Send message to applicant informing them that a nominee declined a nomination
            app(MessageController::class)->notifyApplicantNominationDeclined($NominationNo, $accountNo, $rejectedRoles);

            // Set Nomination status to denied by system
            $Nomination->status = 'N';
            $Nomination->processedBy = null;
            $Nomination->rejectReason = "At least one nomination was rejected.";
            $Nomination->save();
        }
        

        return response()->json(['success'], 200);
    }


    /*
    Gets the roles that an account has been nominated for
    */
    public function getRolesForNominee(Request $request)
    {
        $data = $request->all();
        $nomineeNo = $data['accountNo'];
        $NominationNo = $data['NominationNo'];

        $account = Account::where('accountNo', $nomineeNo)->first();
        $Nomination = Nomination::where('NominationNo', $NominationNo)->first();

        // Check if user exists for given user id
        if ($account == null) {
            // User does not exist, return exception
            return response()->json(['error' => 'Account does not exist.'], 500);
        }

        // Check if Nomination exists for given Nomination No
        if ($Nomination == null) {
            return response()->json(['error' => 'Nomination does not exist.'], 500);
        }

        // Check if user is nominated for that Nomination
        $nominations = Nomination::where('NominationNo', $NominationNo, "and")
            ->where('nomineeNo', $nomineeNo)->get();
        $managerNominations = ManagerNomination::where('NominationNo', $NominationNo, "and")
            ->where('nomineeNo', $nomineeNo)->get();

        if (count($nominations) + count($managerNominations) == 0) {
            return response()->json(['error' => 'Account not nominated for Nomination.'], 500);
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

        foreach ($managerNominations as $nomination) {
            $sub = Account::where('accountNo', $nomination['subordinateNo'])->first();

            array_push(
                $result,
                [
                    "roleName" => "Line Manager for ({$sub->accountNo}) {$sub->fName} {$sub->lName}",
                    "accountRoleId" => "MANAGER",
                    "subordinateNo" => $sub->accountNo,
                    "status" => 'U',
                ]
            );
        }

        return response()->json($result);
    }
}
