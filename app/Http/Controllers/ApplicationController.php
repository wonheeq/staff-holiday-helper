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
}
