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
    private function isSelfNominatedAll($nominations, $accountNo)
    {
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
    public function getStaffMembers(Request $request, String $superiorNo)
    {
        $staffMembers = Account::orderBy('fName')->where('superiorNo', $superiorNo)->get();
        foreach ($staffMembers as $staffMember) {
            $staffMember['pending'] = 'No'; //initialise to random letter
            $applications = Application::where('status', 'U')->get();
            if ($applications->isNotEmpty()) {
                $staffMember['pending'] = 'Yes';
            }
        }
        // error_log($staffMembers);
        return response()->json($staffMembers);
    }

    public function getRolesForStaffs(Request $request, String $staffNo)
    {

        $roleList = array();
        $staffRoles = AccountRole::where('accountNo', $staffNo)->get();
        foreach ($staffRoles as $staffRole) {
            $task = app(RoleController::class)->getRoleFromAccountRoleId($staffRole->accountRoleId);
            array_push($roleList, $task);
        }
        // error_log(implode($roleList));
        return response()->json($roleList);
    }
    /*
    Returns all Applications processed by a manager account number.
    Each application also has the list of the nominations for the application
    */
    public function getManagerApplications(Request $request, String $accountNo)
    {
        $managerApplications = array();
        //Check if there is any applications for the line manager
        $applications = [];
        $users = Account::where('superiorNo', $accountNo)->get();
        foreach ($users as $user) {
            $userApps = Application::where('accountNo', $user->accountNo)->get();
            foreach ($userApps as $app) {
                array_push($applications, $app);
            }
        }


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
                } else {
                    $application["nominations"] = $nominations;
                }
                array_push($managerApplications, $application);
            }
        }
        return response()->json($managerApplications);
    }
}
