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
            $nominee = $nomination['nomineeNo'] != "Self Nomination" ? $nomination['nomineeNo'] : $data['accountNo'];

            $status = 'U';
            // If nomineeNo == applicant accountNo then set status of nomination to 'Y', otherwise keep as 'U'
            if (nominee == $data['accountNo']) {
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
        $newNominationData = $data['nominations'];

        // EDIT APPLICATION
        $application = Application::where('applicationNo', $data['applicationNo'])->first();

        // If self nominated for all, application status should be Undecided
        if ($data['selfNominateAll']) {
            $application->status = 'U';
        }
        else {
            $application->status = 'P';
        }

        // store old dates
        $oldDates = [
            'start' => $application->sDate,
            'end' => $application->eDate,
        ];

        // edit other attributes
        $application->sDate = $this->formatDate($data['sDate']);
        $application->eDate = $this->formatDate($data['eDate']);
        $application->processedBy = null;
        $application->rejectReason = null;
        $application->save();

        // delete old nominations
        Nomination::where('applicationNo', $application->applicationNo)->delete();

        // create new nominations
        foreach ($newNominationData as $nomination) {
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
       
        // Get current line manager account number
        $superiorNo = app(AccountController::class)->getCurrentLineManager($accountNo)->accountNo;
        // Notify manager of edited application
        app(MessageController::class)->notifyManagerApplicationEdited($superiorNo, $applicationNo, $oldDates, $oldNominations->toArray());

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
