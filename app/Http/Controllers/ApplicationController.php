<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\Nomination;
use App\Models\Account;
use \DateTime;
use Illuminate\Support\Facades\Log;

class ApplicationController extends Controller
{
    /*
    Returns all Applications belonging to the given account number.
    Each application also has the list of the nominations for the application
    */
    public function getApplications(Request $request, String $accountNo)
    {
        // Check if user exists for given user id
        if (!Account::where('accountNo', $accountNo)->first()) {
            // User does not exist, return exception
            return response()->json(['error' => 'Account does not exist.'], 500);
        }

        $applications = Application::orderBy('created_at', 'desc')->where('accountNo', $accountNo)->get();
        
        foreach ($applications as $val) {
            // get nominations for application and insert
            $nominations = app(NominationController::class)->getNominations($val["applicationNo"]);
            
            // check if is self nominated for all
            if ($this->isSelfNominatedAll($nominations, $accountNo)) {
                $val['isSelfNominatedAll'] = true;
            }
            else {
                $val["nominations"] = $nominations;
            }
        }

        return response()->json($applications);
    }

    

    /*
    Returns all applications
     */
    public function getAllApplications(Request $request, String $accountNo)
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

            $applications = Application::get();
            return response()->json($applications);  
        }  
    }
/*
    public function getAllApplications(Request $request)
    {   
        $applications = Application::get();
        return response()->json($applications);    
        //return Applications::all(); 
    }
*/
    /*
    Returns true if for all elements, nomineeNo == accountNo
    */
    private function isSelfNominatedAll($nominations, $accountNo) {
        foreach ($nominations as $nomination) {
            if ($nomination['nomineeNo'] != $accountNo) {
                return false;
            }
        }

        return true;
    }

    private function validateApplication($data) {
        // Date is empty
        if ($data['sDate'] == null || $data['eDate'] == null) {
            return false;
        }

        $startDate = new DateTime($data['sDate']);
        $endDate = new DateTime($data['eDate']);
        $currentdate = new DateTime();

        // End date is earlier or equal to start date
        if ($endDate->getTimestamp() - $startDate->getTimestamp() <= 0) {
            return false;
        }

        // A date is in the past
        if ($startDate->getTimestamp() - $currentdate->getTimestamp() <= 0
            || $endDate->getTimestamp() - $currentdate->getTimestamp() <= 0 ) {
            return false;
        }

        // nominations is empty
        if ($data['nominations'] == null || ($data['nominations'] && count($data['nominations']) == 0)) {
            return false;
        }

        if (!$data['selfNominateAll']) {
            $filteredForNull = array_filter($data['nominations'], function($var) {
                if ($var['nomineeNo'] != null) {
                    return $var;
                }
            });
            // a nomineeNo is empty
            if (count($filteredForNull) != count($data['nominations'])) {
                return false;
            }

            $filteredForSelfNom = array_filter($data['nominations'], function($var) {
                if ($var['nomineeNo'] == $data['accountNo']) {
                    return $var;
                }
            });
            // all nominations are self nomination but did not select agreement
            $count1 = count($filteredForSelfNom);
            $count2 = count($data['nominations']);
            Log::debug("{$count1} == {$count2}");
            if (count($filteredForSelfNom) == count($data['nominations'])) {
                return false;
            }
        }

        // An AccountRoleId is null
        foreach ($data['nominations'] as $nomination) {
            if ($nomination['accountRoleId'] == null) {
                return false;
            }
        }

        return true;
    } 

    /*
    Returns a date formatted to mysql timestamp
    */
    private function formatDate(string $date) {
        return str_replace('T', ' ', $date);
    }

    /*
    Creates a new Application in the database if the content is valid.
    Returns an Application encoded in json.
    */
    public function createApplication(Request $request) {
        $data = $request->all();

        if (!$this->validateApplication($data)) {
            return response()->json(['error' => 'Application details invalid.'], 500);
        }

        // If self nominated for all, application status should be Undecided
        if ($data['selfNominateAll']) {
            $application = Application::create([
                'accountNo' => $data['accountNo'],
                'sDate' => $this->formatDate($data['sDate']),
                'eDate' => $this->formatDate($data['eDate']),
                'status' => 'U',
            ]);
        }
        else {
            $application = Application::create([
                'accountNo' => $data['accountNo'],
                'sDate' => $this->formatDate($data['sDate']),
                'eDate' => $this->formatDate($data['eDate']),
                'status' => 'P',
            ]);
        }
       

        // TODO: Implement notifiying of related parties

        foreach ($data['nominations'] as $nomination) {
            // if nomineeNo is Self Nomination, $nominee is applicant accountNo, else the provided nomineeNo
            $nominee = $nomination['nomineeNo'] != "Self Nomination" ? $nomination['nomineeNo'] : $data['accountNo'];

            Nomination::create([
                'applicationNo' => $application->applicationNo,
                'nomineeNo' => $nominee,
                'accountRoleId' => $nomination['accountRoleId'],
                'status' => 'U'
            ]);
        }

        response()->json(['success' => 'success'], 200);
    }

     /*
    Edits an Application in the database if the content is valid.
    Returns an Application encoded in json.
    */
    public function editApplication(Request $request) {
        $data = $request->all();
        $accountNo = $data['accountNo'];
        // Check if user exists for given user id
        if (!Account::where('accountNo', $accountNo)->first()) {
            // User does not exist, return exception
            return response()->json(['error' => 'Account does not exist.'], 500);
        }
    
        if (!$this->validateApplication($data)) {
            return response()->json(['error' => 'Application details invalid.'], 500);
        }

        // Get Old Nominations
        $oldNominations = Nomination::where('applicationNo', $data['applicationNo'])->get();
        $newNominations = $data['nominations'];

        $oldNomineeIds = array();
        $newNomineeIds = array();

        // put nomineeNos into arrays
        foreach ($oldNominations as $nom) {
            array_push($oldNomineeIds, $nom->nomineeNo);
        }
        foreach ($newNominations as $nom) {
            array_push($newNomineeIds, $nom['nomineeNo']);
        }

        $nominationsToDelete = array();
        $nominationsToCreate = array();
        $nominationsToUpdate = array();
     
        // compare newNominations to oldNominations, add to respective array
        foreach ($oldNominations as $old) {
            if (in_array($old->nomineeNo, $newNomineeIds)) {
                // old is in new
                // needs to get updated
                array_push($nominationsToUpdate, $old);
            }
            else {
                // old is not in new
                // needs to get deleted
                array_push($nominationsToDelete, $old);
            }
        }

        foreach ($newNominations as $new) {
            if (!in_array($new, $oldNomineeIds)) {
                // new was NOT in old
                // needs to get created
                array_push($nominationsToCreate, $new);
            }
        }

        // TODO: Implement notifiying of related parties of application edited


        // EDIT APPLICATION
        $application = Application::where('applicationNo', $data['applicationNo'])->first();

        // If self nominated for all, application status should be Undecided
        if ($data['selfNominateAll']) {
            $application->status = 'U';
        }
        else {
            $application->status = 'P';
        }
       
        // edit other attributes
        $application->sDate = $this->formatDate($data['sDate']);
        $application->eDate = $this->formatDate($data['eDate']);
        $application->processedBy = null;
        $application->rejectReason = null;
        $application->save();

        // TODO: Implement notifiying of related parties



        // delete old nominations
        foreach ($oldNominations as $nomination) {
            $obj = Nomination::where('applicationNo', $application->applicationNo, "and")
                        ->where('nomineeNo', $nomination->nomineeNo, "and")
                        ->where('accountRoleId', $nomination->accountRoleId)->delete();
        }

        // create new nominations
        foreach ($newNominations as $nomination) {
            // if nomineeNo is Self Nomination, $nominee is applicant accountNo, else the provided nomineeNo
            $nominee = $nomination['nomineeNo'] != "Self Nomination" ? $nomination['nomineeNo'] : $data['accountNo'];

            if ($nominee == $accountNo) {
                // self nomination so implicity accepted
                Nomination::create([
                    'applicationNo' => $application->applicationNo,
                    'nomineeNo' => $nominee,
                    'accountRoleId' => $nomination['accountRoleId'],
                    'status' => 'Y'
                ]);
            }
            else {
                Nomination::create([
                    'applicationNo' => $application->applicationNo,
                    'nomineeNo' => $nominee,
                    'accountRoleId' => $nomination['accountRoleId'],
                    'status' => 'U'
                ]);
            }
        }
       
        response()->json(['success' => 'success'], 200);
    }


    /*
    Cancels an application by setting it's status to Cancelled
    Deletes Nominations for application
    */
    public function cancelApplication(Request $request, String $accountNo, String $appNo) {
        $applicationNo = intval($appNo);
        // Check if user exists for given user id
        if (!Account::where('accountNo', $accountNo)->first()) {
            // User does not exist, return exception
            return response()->json(['error' => 'Account does not exist.'], 500);
        }

        $application = Application::where('applicationNo', $applicationNo, "and")
        ->where('accountNo', $accountNo)->first();

        // Check if application exists and belongs to the user
        if (!$application)
        {
             // Application does not exist or does not belong to accountNo, return exception
             return response()->json(['error' => 'Application does not exist or does not belong to account.'], 500);
        }

        // Set application status to Cancelled
        $application->status = 'C';
        $application->save();
        // TODO: Implement sending of cancelled application message for user and line manager


        // TODO: Implement sending of cancelled application message for nominees
        $nominations = Nomination::where('applicationNo', $applicationNo)->get();
        foreach ($nominations as $nomination) {
            $nomineeNo = $nomination['nomineeNo'];
        }

        // Delete each nomination associated with the application
        Nomination::where('applicationNo', $applicationNo)->delete();
        return response()->json(['success'], 200);
    }



    /*
    Gets all the details for the specified application
        Period
        Account Roles and Nominees
        Applicant Name
    Route: getApplicationForReview/{accountNo}/{applicationNo}
    */
    public function getApplicationForReview($accountNo, $applicationNo) {
        $applicationNo = intval($applicationNo);

        // Check if user exists for given user id
        $account = Account::where('accountNo', $accountNo)->first();
        if (!$account) {
            // User does not exist, return exception
            return response()->json(['error' => 'Account does not exist.'], 500);
        }

        $application = Application::where('applicationNo', $applicationNo, "and")->first();
        
        // Check that the application exists for the given applicationNo
        if (!$application) {
            return response()->json(['error' => 'Application does not exist.'], 500);
        }

        // Check that the application status is Undecided
        if ($application->status != 'U') {
            return response()->json(['error' => 'Application cannot be reviewed - nominee responses outstanding.'], 500);
        }

        // Check if applicant exists for given application
        $applicant = Account::where('accountNo', $application->accountNo)->first();
        if (!$applicant) {
            // User does not exist, return exception
            return response()->json(['error' => 'Applicant does not exist.'], 500);
        }

        // Check if the user is the superior or substitute superior of the account
        $substituteManager = app(AccountController::class)->getCurrentLineManager($applicant->accountNo);
        if ($accountNo != $substituteManager->accountNo && $accountNo != $applicant->superiorNo) {
            return response()->json(['error' => 'Invalid permissions to review application'], 500);
        }

        $nominationsRaw = [];
        $nominations = Nomination::where('applicationNo', $applicationNo)->get();
        // Iterate through nominations and format
        foreach ($nominations as $nom) {
            // Group by nomineeNo
            // If the nominee's accountNo does not exist as a key, create the default data
            if (!array_key_exists($nom->nomineeNo, $nominationsRaw)) {
                $nominee = Account::where('accountNo', $nom->nomineeNo)->first();
                $nominationsRaw[$nom->nomineeNo] = [
                    "nomineeNo" => $nom->nomineeNo,
                    "nomineeName" => "{$nominee->fName} {$nominee->lName} ({$nominee->accountNo})",
                    "roles" => []
                ];
            }

            // Add to roles
            array_push(
                $nominationsRaw[$nom->nomineeNo]["roles"],
                app(RoleController::class)->getRoleFromAccountRoleId($nom->accountRoleId)
            );
        }

        $nominationsFormatted = [];
        foreach ($nominationsRaw as $n) {
            array_push($nominationsFormatted, $n);
        }

        $data = [
            "applicationNo" => $applicationNo,
            "applicantNo" => $applicant->accountNo,
            "applicantName" => "{$applicant->fName} {$applicant->lName} ({$applicant->accountNo})",
            "duration" => "{$application->sDate} - {$application->eDate}",
            "nominations" => $nominationsFormatted
        ];

        return response()->json($data);
    }


    /*
    Sets the application's status to approved if valid
    */
    public function acceptApplication(Request $request) {
        $data = $request->all();
        $superiorNo = $data['accountNo'];
        $applicationNo = $data['applicationNo'];

        // Check if user exists for given user id
        if (!Account::where('accountNo', $superiorNo)->first()) {
            // User does not exist, return exception
            return response()->json(['error' => 'Account does not exist.'], 500);
        }

        // Check if application exists for hte given applicationNo
        $application = Application::where('applicationNo', $applicationNo)->first();
        if (!$application) {
            return response()->json(['error' => 'Application does not exist.'], 500);
        }

        // Check that the application status is Undecided
        if ($application->status != 'U') {
            return response()->json(['error' => 'Application cannot be reviewed - nominee responses outstanding.'], 500);
        }

        // Check if the superior is the superior of the applicant
        $applicant = Account::where('accountNo', $application->accountNo)->first();
        if (!$applicant) {
            return response()->json(['error' => 'Applicant does not exist.'], 500);
        }

        // Check if the applicant's superior or expected substitute is the given superiorNo
        $expectedSubstitute = app(AccountController::class)->getCurrentLineManager($applicant->accountNo);
        if ($superiorNo != $applicant->superiorNo && $superiorNo != $expectedSubstitute->accountNo) {
            return response()->json(['error' => 'Account is not the superior of applicant.'], 500);
        }

        // Confirm that all nominees agreed
        $nominations = Nomination::where("applicationNo", $applicationNo)->get();
        foreach ($nominations as $n) {
            if ($n->status != "Y") {
                return response()->json(['error' => 'A nomination was not accepted.'], 500);
            }
        }

        // set application status to 'Y'
        // set processedBy indicator to superiorNo
        $application->status = 'Y';
        $application->processedBy = $superiorNo;
        $application->save();

        // Mark Application Awaiting Review message as acknowledged
        $message = Message::where('applicationNo', $applicationNo, "and")

        ->where('senderNo', $applicant->accountNo, "and")
        ->where('subject', "Application Awaiting Review")->first();

        if($message != null){
            $message->acknowledged = true;
            $message->save();
        }

        // Message applicant that their application was approved.
        app(MessageController::class)->notifyApplicantApplicationDecision($superiorNo, $applicationNo, true, null);
        
        return response()->json(['success' => 'success'], 200);
    }

    /*
    Sets the application's status to rejected if valid
    */
    public function rejectApplication(Request $request) {
        $data = $request->all();
        $superiorNo = $data['accountNo'];
        $applicationNo = $data['applicationNo'];
        $rejectReason = $data['rejectReason'];

        // Check if user exists for given user id
        if (!Account::where('accountNo', $superiorNo)->first()) {
            // User does not exist, return exception
            return response()->json(['error' => 'Account does not exist.'], 500);
        }

        // Check if application exists for hte given applicationNo
        $application = Application::where('applicationNo', $applicationNo)->first();
        if (!$application) {
            return response()->json(['error' => 'Application does not exist.'], 500);
        }

        // Check that the application status is Undecided
        if ($application->status != 'U') {
            return response()->json(['error' => 'Application cannot be reviewed - nominee responses outstanding.'], 500);
        }

        // Check if the superior is the superior of the applicant
        $applicant = Account::where('accountNo', $application->accountNo)->first();
        if (!$applicant) {
            return response()->json(['error' => 'Applicant does not exist.'], 500);
        }

        // Check if the applicant's superior or expected substitute is the given superiorNo
        $expectedSubstitute = app(AccountController::class)->getCurrentLineManager($applicant->accountNo);
        if ($superiorNo != $applicant->superiorNo && $superiorNo != $expectedSubstitute->accountNo) {
            return response()->json(['error' => 'Account is not the superior of applicant.'], 500);
        }

        // Confirm that reject reason is not null or empty
        if ($rejectReason == null || $rejectReason == "") {
            return response()->json(['error' => 'Reject message cannot be empty.'], 500);
        }

        // set application status to 'N'
        $application->status = 'N';
        $application->processedBy = $superiorNo;
        $application->rejectReason = $rejectReason;
        $application->save();

        // Mark Application Awaiting Review message as acknowledged
        $message = Message::where('applicationNo', $applicationNo, "and")
        ->where('senderNo', $applicant->accountNo, "and")
        ->where('subject', "Application Awaiting Review")->first();
        if($message != null){
            $message->acknowledged = true;
            $message->save();
        }
        
        // Message applicant that their application was approved.
        app(MessageController::class)->notifyApplicantApplicationDecision($superiorNo, $applicationNo, false, $rejectReason);
        
        return response()->json(['success' => 'success'], 200);
    }
}
