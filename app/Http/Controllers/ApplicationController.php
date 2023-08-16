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
                'nomineeNo' => $nominee,
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
        else {
            // Not all nominations were self-nominations, group together roles and inform all nominees
            app(MessageController::class)->notifyNomineesApplicationCreated($application->applicationNo);
        }

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
    Handles deleting of old nominations from the database
    Handles sending grouped messages notifying nominees of cancelled nominations
    */
    public function handleEditApplicationCancelledNominations($oldNominations, $newNominations) {
        // Iterate through new nomination data
        foreach($newNominations as $new) {
            // Iterate through old nominations
            foreach ($oldNominations as $old) {
                if ($old->nomineeNo == $new['nomineeNo'])
            }
        }
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
        // store old dates
        $oldDates = [
            'start' => $application->sDate,
            'end' => $application->eDate,
        ];

        // EDIT APPLICATION
        $application = $this->handleEditApplication($data);
        
        // Delete old nominations where nomineeNo and accountRoleId not found in new nominations
        $this->handleEditApplicationCancelledNominations($oldNominations, $data['nominations']);
        // delete old nominations
        //Nomination::where('applicationNo', $application->applicationNo)->delete();

        /*
        $newSubRequests = [];
        $outOfRangeSubRequests = [];

        // create new nominations
        foreach ($data['nominations'] as $nomination) {
            // if nomineeNo is Self Nomination, $nominee is applicant accountNo, else the provided nomineeNo
            $nominee = $nomination['nomineeNo'] != "Self Nomination" ? $nomination['nomineeNo'] : $data['accountNo'];
            $status = 'U';
            $foundOldNomination = false;
            if ($nominee == $accountNo) {
                // self nomination so implicity accepted
                $status = 'Y';
            }
            else {
                // Use old status if it nomineeNo and period weren't changed or is subset
                if ($application->sDate >= $oldDates['start'] && $application->eDate <= $oldDates['end']) {
                    // find old status

                    // iterate through old nominations to find old status for the 'new' nomination
                    foreach ($oldNominations as $old) {
                        // check that the old data == new data
                        if ($old->nomineeNo == $nominee && $old->accountRoleId == $nomination['accountRoleId']) {
                            $status = $old->status;
                            $foundOldNomination = true;
                            break;
                        }
                    }

                    if (!$foundOldNomination) {
                        // Did not find old nomination matching new nomination data therefore, the nomination is completely new 
                        // Add to list of sub request messages to create for new nominations
                        if ($newSubRequests[$nomination->nomineeNo] == null) {
                            $newSubRequests[$nomination->nomineeNo] = array();
                        }
                        
                        // Get role name
                        $roleName = app(RoleController::class)->getRoleFromAccountRoleId($nomination['accountRoleId']);

                        array_push($newSubRequests[$nomination->nomineeNo], $roleName);
                    }
                }
                // Is not same period or subset therefore is out of range
                else {
                    // Add to list of sub request messages to create for out of range period nominations
                    if ($outOfRangeSubRequests[$nomination->nomineeNo] == null) {
                        $outOfRangeSubRequests[$nomination->nomineeNo] = array();
                    }
                    
                    // Get role name
                    $roleName = app(RoleController::class)->getRoleFromAccountRoleId($nomination['accountRoleId']);

                    array_push($outOfRangeSubRequests[$nomination->nomineeNo], $roleName);
                }
            }

            Nomination::create([
                'applicationNo' => $application->applicationNo,
                'nomineeNo' => $nominee,
                'accountRoleId' => $nomination['accountRoleId'],
                'status' => $status
            ]);
        }
       
        // Get current line manager account number
        $superiorNo = app(AccountController::class)->getCurrentLineManager($accountNo)->accountNo;
        // Notify manager of edited application - I don't think we need this?
        //app(MessageController::class)->notifyManagerApplicationEdited($superiorNo, $applicationNo, $oldDates, $oldNominations->toArray());

        // Handle notifying of nominees of edited application
        app(MessageController::class)->handleNotifyNomineeApplicationEdited($applicationNo, $oldDates, $oldNominations);
        
        foreach ($outOfRangeSubRequests as $receiverNo => $subRequestRoles) {
            // Delete old substitution request messages for outOfRangeSubRequests
            // This is required so that there isn't an outdated substitution request message that a user can interact with
            Message::where('applicationNo', $application->applicationNo, "and")
            ->where('subject', 'Substitution Request', "and")
            ->where('receiverNo', $receiverNo)->delete();

            $count = count($subRequestRoles);
            $content = [
                "This application has been edited, please accept or reject for the updated details:",
                "You have been nominated for {$count} roles:",
            ];

            // Add each role in subRequestRoles to content
            foreach ($subRequestRoles as $roleName) {
                array_push(
                    $content,
                    "→{$roleName}"
                );
            }

            array_push(
                $content,
                "Duration: {$application['sDate']} - {$application['eDate']}"
            );

            // Create new updated substitution request message
            Message::create([
                'applicationNo' => $application->applicationNo,
                'senderNo' => $application->accountNo,
                'receiverNo' => $receiverNo,
                'subject' => 'Substitution Request',
                'content' => json_encode($content),
                'acknowledged' => false,

            ]);
        }

        foreach ($newSubRequests as $receiverNo => $subRequestRoles) {
            $count = count($subRequestRoles);
            $content = [
                "You have been nominated for {$count} roles:",
            ];

            // Add each role in subRequestRoles to content
            foreach ($subRequestRoles as $roleName) {
                array_push(
                    $content,
                    "→{$roleName}"
                );
            }

            array_push(
                $content,
                "Duration: {$application['sDate']} - {$application['eDate']}"
            );

            // Create new updated substitution request message
            Message::create([
                'applicationNo' => $application->applicationNo,
                'senderNo' => $application->accountNo,
                'receiverNo' => $receiverNo,
                'subject' => 'Substitution Request',
                'content' => json_encode($content),
                'acknowledged' => false,

            ]);
        }
*/
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
}
