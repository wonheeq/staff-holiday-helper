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
            $val["nominations"] = $nominations;
        }

        return response()->json($applications);
    }

    private function validateApplication($data) {
        Log::debug("pass1");
        // Date is empty
        if ($data['sDate'] == null || $data['eDate'] == null) {
            return false;
        }

        $startDate = new DateTime($data['sDate']);
        $endDate = new DateTime($data['eDate']);
        $currentdate = new DateTime();

        // End date is earlier or equal to start date
        Log::debug($endDate->getTimestamp() - $startDate->getTimestamp());
        if (date_diff($endDate, $startDate)->invert == 1 ) {
            return false;
        }

        Log::debug("pass2");
        // A date is in the past
        if (date_diff($startDate, $currentdate)->invert == 1 || date_diff($endDate, $currentdate)->invert == 1) {
            return false;
        }

        Log::debug("pass3");
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

            Log::debug("pass4");
            $filteredForSelfNom = array_filter($data['nominations'], function($var) {
                if ($var['nomineeNo'] == "Self Nomination") {
                    return $var;
                }
            });
            // all nominations are self nomination but did not select agreement
            if (count($filteredForSelfNom) == count($data['nominations'])) {
                return false;
            }


        Log::debug("pass5");
        }
        return true;
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

        $application = Application::create([
            'accountNo' => $data['accountNo'],
            'sDate' => $data['sDate'],
            'eDate' => $data['eDate'],
            'status' => 'P',
        ]);

        foreach ($data['nominations'] as $nomination) {
            // if nomineeNo is Self Nomination, $nominee is applicant accountNo, else the provided nomineeNo
            $nominee = $nomination['nomineeNo'] != "Self Nomination" ? $nomination['nomineeNo'] : $data['accountNo'];
            Nomination::create([
                'applicationNo' => $application['applicationNo'],
                'nomineeNo' => $nominee,
                'accountRoleId' => $nomination['accountRoleId'],
                'status' => 'U'
            ]);
        }

        return response()->json($application);
    }
}
