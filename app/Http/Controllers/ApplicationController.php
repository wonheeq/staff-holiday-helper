<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\Nomination;
use App\Models\Account;
use App\Models\Message;
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
            
            $filteredForSelfNom = array_filter($data['nominations'], function($var) use($data){
                if ($var['nomineeNo'] == $data['accountNo']) {
                    return $var;
                }
            });
            // all nominations are self nomination but did not select agreement
            $count1 = count($filteredForSelfNom);
            $count2 = count($data['nominations']);
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

        $application = null;

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
       
        // Generate nominations for application
        foreach ($data['nominations'] as $nomination) {
            // if nomineeNo is Self Nomination, $nominee is applicant accountNo, else the provided nomineeNo
            $status = 'U';
            // If nomineeNo == applicant accountNo then set status of nomination to 'Y', otherwise keep as 'U'
            if ($nomination['nomineeNo'] == $data['accountNo']) {
                $status = 'Y';
            }

            Nomination::create([
                'applicationNo' => $application->applicationNo,
                'nomineeNo' => $nomination['nomineeNo'],
                'accountRoleId' => $nomination['accountRoleId'],
                'status' => $status
            ]);
        }


        // Inform line manager of new application to review (if self-nominated all) 
        if ($application->status == 'U') {
            // Get current line manager account number
            $superiorNo = app(AccountController::class)->getCurrentLineManager($data['accountNo'])->accountNo;
            // Notify line manager of new application to review
            app(MessageController::class)->notifyManagerApplicationAwaitingReview($superiorNo, $application->applicationNo);
        }
        // Not all nominations were self-nominations, group together roles and inform all nominees
        app(MessageController::class)->notifyNomineesApplicationCreated($application->applicationNo);

        response()->json(['success' => 'success'], 200);
    }

    /*
    handles the logic for updating and saving the application
    */
    public function handleEditApplication(array $data) {
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

        return $application;
    }

    /*
    Checks if each nominee from the old nominations has been removed as a nomination in any of the new nominations
    Handles deleting of old nominations from the database
    Handles sending grouped messages notifying nominees of cancelled nominations
    */
    public function handleEditApplicationCancelledNominations($oldNominations, $newNominations, $applicationNo) {
        $removedNominations = [];
        
        
        // Iterate through old nomination data
        foreach($oldNominations as $old) {
            $foundInNew = false;
            // Iterate through new nominations
            foreach ($newNominations as $new) {
                // Check if we can find the old data in the new
                if ($old->nomineeNo == $new['nomineeNo'] && $old->accountRoleId == $new['accountRoleId']) {
                    $foundInNew = true;
                    break;
                }
            }
            // If the old nomination data wasn't found in the new nomination data 
                // AKA The nominee was removed as a nominee
            if (!$foundInNew) {
                // Delete old nomination from database
                Nomination::where('applicationNo', $applicationNo, "and")
                    ->where('nomineeNo', $old->nomineeNo, "and")
                    ->where('accountRoleId', $old->accountRoleId)->delete();

                // delete message associated with old nomination/s
                $message = Message::where('applicationNo', $applicationNo, "and")
                    ->where('receiverNo', $old->nomineeNo, "and")
                    ->where('subject', "Substitution Request")->delete();

                // create new array with nomineeNo as key inside removedNominations if it doesn't exist
                if (!array_key_exists($old->nomineeNo, $removedNominations)) {
                    $removedNominations[$old->nomineeNo] = array();
                }

                // Add to list of accountRoleIds the nominee was removed as a nominee for
                array_push($removedNominations[$old->nomineeNo], $old->accountRoleId);
            }
        }
        // call method to create new messages
        app(MessageController::class)->notifyNomineeNominationCancelled($removedNominations, $applicationNo);
    }
    
    // Handle nonedited, edited nominations and creation of new nominations
    public function handleEditApplicationNonCancelledNominations($oldNominations, $newNominations, $applicationNo, $oldDates) {
        $application = Application::where('applicationNo', $applicationNo)->first();
        
        $nonEditedNominations = [];
        $editedNominations = [];
        $toNewlyCreateNominations = [];
        $isSubset = true;

        // Check if it is possibly a subset AKA in original range at least
        if ($application->sDate >= $oldDates['start'] && $application->eDate <= $oldDates['end']) {
            // check that the period is not exactly the same
            if ($application->sDate == $oldDates['start'] && $application->eDate == $oldDates['end']) {
                $isSubset = false;
            }
        }

        // Iterate through new nomination data
        foreach($newNominations as $new) {
            $newInOld = false;
            // Iterate through old nominations
            foreach ($oldNominations as $old) {
                // Check if we can find entry that matches the accountRoleId of new
                if ($old->accountRoleId == $new['accountRoleId']) {
                    $newInOld = true;
                    // Keep old status if period not out of range
                    $status = $old->status;
                    

                    // Set isSubset to false since at least one nomination was rejected
                    // But the nominee was again nominated for the nomination, in the new edited application
                    // Therefore, we will delete the old message to approve or reject the old nominations
                    // And resend a new message for the new nominations
                    if ($status == 'N') {
                        $isSubset = false;
                    }

                    // If it changed then it can't be solely a subset
                    if ($old->nomineeNo != $new['nomineeNo']) 
                    {
                        $isSubset = false;
                    }

                    // Edit status IF period has been altered to be out of range.
                    if ($application->sDate >= $oldDates['start'] && $application->eDate <= $oldDates['end']) {
                        // Empty on purpose
                    }
                    else {
                        //out of range, set status to Undecided
                        $status = 'U';
                        $isSubset = false;
                    }

                    // Update Nomination if status or nomineeNo has changed 
                    if ($status != $old->status || $old->nomineeNo != $new['nomineeNo']) {
                        // Delete old Substitution Request message for this application and receiver (nomineeNo)
                        Message::where('applicationNo', $applicationNo, "and")
                        ->where('subject', 'Substitution Request', 'and')
                        ->where('receiverNo', $old->nomineeNo)->delete();

                        Nomination::where('applicationNo', $applicationNo, "and")
                        ->where('nomineeNo', $old->nomineeNo, "and")
                        ->where('accountRoleId', $old->accountRoleId)
                        ->update([
                            "status" => $status,
                            "nomineeNo" => $new['nomineeNo']
                        ]);

                        // add to editedNominations
                        if (!array_key_exists($new['nomineeNo'], $editedNominations)) {
                            $editedNominations[$new['nomineeNo']] = [];
                        }

                        array_push($editedNominations[$new['nomineeNo']], $old->accountRoleId);
                    }
                    else {
                        // add to nonEditedNominations
                        if (!array_key_exists($old['nomineeNo'], $nonEditedNominations)) {
                            $nonEditedNominations[$old['nomineeNo']] = [];
                        }

                        array_push($nonEditedNominations[$old['nomineeNo']], $old->accountRoleId);
                    }
                    break;
                }
            }

            if (!$newInOld) {
                $isSubset = false;
                // new not found in old data, therefore a nomination was created for an accountRoleId that previously was not assigned to the user
                // Create new nomination
                Nomination::create([
                    'applicationNo' => $applicationNo,
                    'nomineeNo' => $new['nomineeNo'],
                    'accountRoleId' => $new['accountRoleId'],
                    // status = 'Y' if self nominated, otherwise = 'U'
                    'status' => $new['nomineeNo'] == $application->accountNo ? 'Y' : 'U',
                ]);

                // add to toNewlyCreateNominations
                if (!array_key_exists($new['nomineeNo'], $toNewlyCreateNominations)) {
                    $toNewlyCreateNominations[$new['nomineeNo']] = [];
                }

                array_push($toNewlyCreateNominations[$new['nomineeNo']], $new['accountRoleId']);
            }
        }

        // group together nominations and generate messages
        $groupedNominations = [];

        foreach ($editedNominations as $nomineeNo => $accountRoleIds) {
            if (!array_key_exists($nomineeNo, $groupedNominations)) {
                $groupedNominations[$nomineeNo] = $accountRoleIds;
            }
            else {
                $groupedNominations[$nomineeNo] = array_merge($groupedNominations[$nomineeNo], $accountRoleIds);
            }
        }

        foreach ($toNewlyCreateNominations as $nomineeNo => $accountRoleIds) {
            if (!array_key_exists($nomineeNo, $groupedNominations)) {
                $groupedNominations[$nomineeNo] = $accountRoleIds;
            }
            else {
                $groupedNominations[$nomineeNo] = array_merge($groupedNominations[$nomineeNo], $accountRoleIds);
            }
        }

        foreach ($nonEditedNominations as $nomineeNo => $accountRoleIds) {
            $shouldSendEditedMessageForNominee = false;

            // Check foreach nomination if the status was not previously accepted
            foreach ($accountRoleIds as $accountRoleId) {
                $theNomination = Nomination::where('applicationNo', $applicationNo, "and")
                ->where('nomineeNo', $nomineeNo, "and")
                ->where("accountRoleId", $accountRoleId)->first();

                if ($theNomination->status != 'Y') {
                    // One of the nominations for the nominee was previously rejected or just not responded to
                    // So we exit this for loop
                    // and set a flag to handle this outside of this loop
                    $shouldSendEditedMessageForNominee = true;
                    break;
                }
            }
            
            if ($shouldSendEditedMessageForNominee) {
                // One of the nominations for the nominee was previously rejected, or just not responded to
                // Now we delete all old nominations for the nominee and recreate them with status 'U'
                    
                Nomination::where('applicationNo', $applicationNo, "and")
                ->where('nomineeNo', $nomineeNo)->delete();

                // For each accountRoleId, create new nomination
                foreach ($accountRoleIds as $accountRoleId) {
                    Nomination::create([
                        'applicationNo' => $applicationNo,
                        'nomineeNo' => $nomineeNo,
                        'accountRoleId' => $accountRoleId,
                        'status' => 'U' 
                    ]);
                }

                // Check if groupedNominations contains the nomineeNo
                if (!array_key_exists($nomineeNo, $groupedNominations)) {
                    // set the subarray as accountRoleId if it doesn't exist already
                    $groupedNominations[$nomineeNo] = $accountRoleIds;
                }
                else {
                    // Merge the accountRoleIds in
                    $groupedNominations[$nomineeNo] = array_merge($groupedNominations[$nomineeNo], $accountRoleIds);
                }

                // just in case, set isSubset to false again
                $isSubset = false;
            }
            else if (!array_key_exists($nomineeNo, $groupedNominations)) {
                // DO NOTHING, do not group non edited nominations if not existing already due to edited or new nominations
                // We do not need to resend the Substitution Request message for nominations that have not been edited or whenever the period has not changed or is a subset 
            }
            else {
                $groupedNominations[$nomineeNo] = array_merge($groupedNominations[$nomineeNo], $accountRoleIds);
            }
        }

        if ($isSubset) {
            app(MessageController::class)->notifyNomineeApplicationEditedSubsetOnly($applicationNo);
        }
        else {
            app(MessageController::class)->notifyNomineeApplicationEdited($applicationNo, $groupedNominations);
        }
    }

    /*
    Edits an Application in the database if the content is valid.
    Returns an Application encoded in json.
    */
    public function editApplication(Request $request) {
        $data = $request->all();
        $accountNo = $data['accountNo'];
        $applicationNo = $data['applicationNo'];
        $application = Application::where('applicationNo', $applicationNo)->first();
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
        // store old dates
        $oldDates = [
            'start' => $application->sDate,
            'end' => $application->eDate,
        ];

        // EDIT APPLICATION
        $application = $this->handleEditApplication($data);
        
        // Delete old nominations where nomineeNo and accountRoleId not found in new nominations
        $this->handleEditApplicationCancelledNominations($oldNominations, $data['nominations'], $applicationNo);
        // Handle nonedited, edited nominations and creation of new nominations
        $this->handleEditApplicationNonCancelledNominations($oldNominations, $data['nominations'], $applicationNo, $oldDates);
        
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

        // Get current line manager account number
        $superiorNo = app(AccountController::class)->getCurrentLineManager($accountNo)->accountNo;
        // Notify manager of application cancellation if status was undecided or approved
        if ($application->status == 'Y' || $application->status == "U") {
            // Delete old message
            Message::where('receiverNo', $superiorNo, 'and')
            ->where('senderNo', $accountNo, 'and')
            ->where('applicationNo', $applicationNo, 'and')
            ->where('subject', "Application Awaiting Review")->delete();

            app(MessageController::class)->notifyManagerApplicationCancelled($superiorNo, $applicationNo);
        }

        // Set application status to Cancelled
        $application->status = 'C';
        $application->save();

        $processedNominees = [$accountNo];
        // Send cancelled application message for nominees
        $nominations = Nomination::where('applicationNo', $applicationNo)->get();
        foreach ($nominations as $nomination) {
            $nomineeNo = $nomination['nomineeNo'];

            // Check if nomineeNo is not in processedNominees
            if (!in_array($nomineeNo, $processedNominees)) {
                array_push($processedNominees, $nomineeNo);

                app(MessageController::class)->notifyNomineeApplicationCancelled($nomineeNo, $applicationNo);
            }
        }

        // Delete each 'Substitution Request' Message for the application
        Message::where('applicationNo', $applicationNo, "and")
            ->where('subject', "Substitution Request")->delete();

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

        $application = Application::where('applicationNo', $applicationNo, "and")
        ->where('accountNo', $accountNo)->first();
        
        // Check that the application exists for the given applicationNo
        if (!$application) {
            return response()->json(['error' => 'Application does not exist.'], 500);
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

        $nominationsFormatted = [];
        $nominations = Nomination::where('applicationNo', $applicationNo)->get();
        // Iterate through nominations and format
        foreach ($nominations as $nom) {
            // Group by nomineeNo
            // If the nominee's accountNo does not exist as a key, create the default data
            if (!array_key_exists($nom->nomineeNo)) {
                $nominee = Account::where('accountNo', $nom->nomineeNo)->first();
                $nominationsFormatted[$nom->nomineeNo] = [
                    "nomineeName" => "{$nominee->firstName} {$nominee->lastName} ({$nominee->accountNo})",
                    "roles" => []
                ];
            }

            // Add to roles
            array_push(
                $nominationsFormatted[$nom->nomineeNo]["roles"],
                app(RoleController::class)->getRoleFromAccountRoleId($nom->accountRoleId)
            );
        }

        $data = [
            "applicantName" => "{$applicant->firstName} {$applicant->lastName} ({$applicant->accountNo})",
            "period" => [
                "start" => $application->sDate,
                "end" => $application->eDate,
            ],
            "nominations" => $nominationsFormatted
        ];

        return response()->json($data);
    }
}
