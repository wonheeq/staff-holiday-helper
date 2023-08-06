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

        if (!$data['selfNominateAll'] == 1) {
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
                if ($var['nomineeNo'] == "Self Nomination") {
                    return $var;
                }
            });
            // all nominations are self nomination but did not select agreement
            if (count($filteredForSelfNom) == count($data['nominations'])) {
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
        if ($data['selfNominateAll'] == 1) {
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
}
