<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Application;
use App\Models\Nomination;
use App\Models\AccountRole;
use App\Models\Role;



use Illuminate\Http\Request;

class ManagerController extends Controller
{
    private function isSelfNominatedAll($nominations, $accountNo) {
        foreach ($nominations as $nomination) {
            if ($nomination['nomineeNo'] != $accountNo) {
                return false;
            }
        }
        return true;
    }
    /**
     * Get all staff members under a particular line manger
     */
    public function getStaffMembers(Request $request, String $superiorNo){
        $staffMembers = Account::orderBy('fName')->where('superiorNo', $superiorNo)->get();
        foreach($staffMembers as $staffMember){
            $staffMember['pending'] = 'No'; //initialise to random letter
            $applications = Application::where('status', 'U')->get();
            if($applications->isNotEmpty())
            {
                $staffMember['pending'] = 'Yes';
            }
        }
        // error_log($staffMembers);
        return response()->json($staffMembers);
    }
    public function getRolesForStaffs(Request $request, String $staffNo){
        
        $roleList = array();
        $staffRoles = AccountRole::where('accountNo', $staffNo)->get();
        foreach($staffRoles as $staffRole){
            $role = Role::where('roleId', $staffRole['roleId'])->first();
            $roleName = $role['name'];
            $task = "UNIT CODE: {$roleName}";

            array_push($roleList, $task);
        }
        // error_log(implode($roleList));
        return response()->json($roleList);
    }
    /*
    Approve an application by setting it's status to Y
    */
    public function acceptApplication(Request $request) {
        $data = $request->all();
        $accountNo = $data['accountNo'];
        $applicationNo = $data['applicationNo'];
        
        $applicant = Account::where('accountNo', $accountNo)->first();
        $application = Application::where('applicationNo', $applicationNo)->first();

        // Check if user exists for given user id
        if ($applicant == null) {
            // Applicant does not exist, return exception
            return response()->json(['error' => 'Applicant does not exist.'], 500);
        }

        // Check if application exists for given application No
        if ($application == null) {
            return response()->json(['error' => 'Application does not exist.'], 500);
        }
        if($data['processedBy'] != null){
            if($application['processedBy'] != $data['processedBy']){
                return response()->json(['error' => 'Wrong line manager.'], 500);
            }
        }
        
        // Set application status to Yes
        $application->status = 'Y';
        $application->save();
        // TODO: Implement sending of approved application message for applicant and nominees.

        return response()->json(['success'], 200);
    }
/*
    *Line Manager*
    Reject an application by setting it's status to N
    */
    public function rejectApplication(Request $request) {
        $data = $request->all();
        $accountNo = $data['accountNo'];
        $applicationNo = $data['applicationNo'];
        $rejectReason = $data['rejectReason'];
        
        $applicant = Account::where('accountNo', $accountNo)->first();
        $application = Application::where('applicationNo', $applicationNo)->first();

        // Check if user exists for given user id
        if ($applicant == null) {
            // Applicant does not exist, return exception
            return response()->json(['error' => 'Applicant does not exist.'], 500);
        }

        // Check if application exists for given application No
        if ($application == null) {
            return response()->json(['error' => 'Application does not exist.'], 500);
        }
        if($data['processedBy'] != null){
            if($application['processedBy'] != $data['processedBy']){
                return response()->json(['error' => 'Wrong line manager.'], 500);
            }
        }
        // Set application status to No
        $application->status = 'N';
        $application->rejectReason = $rejectReason;
        $application->save();

        // TODO: Implement sending of approved application message for applicant and nominees.

        // Delete each nomination associated with the application
        Nomination::where('applicationNo', $applicationNo)->delete();
        return response()->json(['success'], 200);
    }
    /*
    Returns all Applications processed by a manager account number.
    Each application also has the list of the nominations for the application
    */
    public function getManagerApplications(Request $request, String $accountNo)
    {
        $managerApplications=array();
        // $statusOrder =['U', 'Y', 'N'];
        //Check if there is any applications for the line manager
        if(!Application::where('processedBy', $accountNo) ->count() === 0){
            return response()->json(['error' => 'There is no applications currently.'], 500);
        }
        $applications = Application::orderBy('created_at', 'desc')->where('processedBy', $accountNo)->get();
        foreach ($applications as $application) {
            // Add in applicant name for each application
            if ($application['accountNo'] != null && $application['status'] != 'P') {
                // if application account number is not null, then applicant is a user
                $applicant = app(UserController::class)->getUser($application["accountNo"]);
                $application['applicantName'] = "{$applicant['fName']} {$applicant['lName']}";
                $nominations = app(NominationController::class)->getNominations($application["applicationNo"]);

                // check if is self nominated for all
                if ($this->isSelfNominatedAll($nominations, $accountNo)) {
                    $application['isSelfNominatedAll'] = true;
                }
                else {
                    $application["nominations"] = $nominations;
                }
                array_push($managerApplications,$application);
            }
        }
        //sorted by status
        // usort($managerApplications, function($appOne, $appTwo) use ($statusOrder) {
        //     $appOneIndex = array_search($appOne["status"], $statusOrder);
        //     $appTwoIndex = array_search($appTwo["status"], $statusOrder);
            
        //     return $appOneIndex - $appTwoIndex;
        // });
        return response()->json($managerApplications);
    }
}
