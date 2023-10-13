<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Application;
use App\Models\Nomination;
use App\Models\AccountRole;
use App\Models\Unit;
use App\Models\Major;
use App\Models\Course;
use App\Models\Role;
use App\Models\ManagerNomination;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\Log;
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
    public function addStaffRole(Request $request){
        $data = $request->all();
        $staffNo = $data['staffNo'];
        $unitCode = $data['unitCode'];
        $roleName = $data['roleName'];
        $staff = Account::where('accountNo', $staffNo)->first();

        //Unit, Major and Course does not exist, return exception
        if(!Unit::where('unitId', $unitCode)->first()){
            if(!Major::where('majorId', $unitCode)->first()){
                if(!Course::where('courseId', $unitCode)->first()){
                    return response()->json(['error' => 'Unit does not exist.'], 500);
                }
            }
        }

        //Check if role exists
        if(!Role::where('name', $roleName)->first()){
            return response()->json(['error' => 'Role does not exist.'], 500);
        }

        //Staff already has the unit role, return exception
        $existRole = Role::where('name', $roleName)->first();
        if(AccountRole::where('accountNo', $staffNo, "and")
        ->where('unitId', $unitCode)->get()){
            //Check all the roles under this unit
            $staffRoles = AccountRole::where('accountNo', $staffNo, "and")
            ->where('unitId', $unitCode)->get();
            foreach($staffRoles as $staffRole){
                if($staffRole['roleId'] == $existRole['roleId']){
                    return response()->json(['error' => 'Staff already has this role.'], 500);
                }
            }
        }

        //Staff already has the major role, return exception
        if(AccountRole::where('accountNo', $staffNo, "and")
        ->where('majorId', $unitCode)->get()){
            //Check all the roles under this major
            $staffRoles = AccountRole::where('accountNo', $staffNo, "and")
            ->where('majorId', $unitCode)->get();
            foreach($staffRoles as $staffRole){
                if($staffRole['roleId'] == $existRole['roleId']){
                    return response()->json(['error' => 'Staff already has this role.'], 500);
                }
            }
        }

        //Staff already has the course role, return exception
        if(AccountRole::where('accountNo', $staffNo, "and")
        ->where('courseId', $unitCode)->get()){
            //Check all the roles under this course
            $staffRoles = AccountRole::where('accountNo', $staffNo, "and")
            ->where('courseId', $unitCode)->get();
            foreach($staffRoles as $staffRole){
                if($staffRole['roleId'] == $existRole['roleId']){
                    return response()->json(['error' => 'Staff already has this role.'], 500);
                }
            }
        }

        // Check if role is for major coordinator
        if ($roleName == "Major Coordinator") {
            //Create major role
            $newRole = Role::where('name', $roleName)->first();
            AccountRole::create([
            'accountNo' => $staffNo,
            'roleId' => $newRole['roleId'],
            'majorId' => $unitCode,
            'schoolId' => $staff['schoolId']]);
            return response()->json(['success' => 'success'], 200);
        }
        // Check if role is for course coordinator
        else if ($roleName == "Course Coordinator") {
            // Create course role
            $newRole = Role::where('name', $roleName)->first();
            AccountRole::create([
            'accountNo' => $staffNo,
            'roleId' => $newRole['roleId'],
            'courseId' => $unitCode,
            'schoolId' => $staff['schoolId']]);
            return response()->json(['success' => 'success'], 200);
        }
        else if($roleName == "Tutor" || $roleName =="Unit Coordinator" || $roleName == "Lecturer"){
            $newRole = Role::where('name', $roleName)->first();
            //Create unit role for the account
            AccountRole::create([
                'accountNo' => $staffNo,
                'roleId' => $newRole['roleId'],
                'unitId' => $unitCode,
                'schoolId' => $staff['schoolId']
            ]);
            return response()->json(['success' => 'success'], 200);
        }
        else{
            return response()->json(['error' => 'Fail to create role.', 500]);
        }
    }
    public function removeStaffRole(Request $request){
        $data = $request->all();
        $staffNo = $data['staffNo'];
        $unitCode = $data['unitCode'];
        $roleName = $data['roleName'];
        //Check if staff exist, return exception
        if(!Account::where('accountNo', $staffNo)->first()){
            return response()->json(['error' => 'Account does not exist.'], 500);
        }

        $existRole = Role::where('name', $roleName)->first();



        //Check if it is a unit, then remove
        if((AccountRole::where('accountNo', $staffNo, "and")
        ->where('unitId', $unitCode)->get()) && ($roleName == 'Tutor'|| $roleName == 'Lecturer' || $roleName == 'Unit Coordinator')){
            //Check all the roles under this unit
            $staffRoles = AccountRole::where('accountNo', $staffNo, "and")
            ->where('unitId', $unitCode)->get();
                foreach($staffRoles as $staffRole){
                if($staffRole['roleId'] == $existRole['roleId']){
                    Nomination::where('accountRoleId', $staffRole['accountRoleId'])->delete();
                    AccountRole::where('accountRoleId', $staffRole['accountRoleId'])->delete();
                                        return response()->json(['success' => 'success'], 200);
                    }
            }
        }

        //Check if it is a major, then remove
        if((AccountRole::where('accountNo', $staffNo, "and")
        ->where('majorId', $unitCode)->get()) && $roleName == 'Major Coordinator'){
            //Check all the roles under this major
            $staffRoles = AccountRole::where('accountNo', $staffNo, "and")
            ->where('majorId', $unitCode)->get();
            foreach($staffRoles as $staffRole){
                if($staffRole['roleId'] == $existRole['roleId']){
                    Nomination::where('accountRoleId', $staffRole['accountRoleId'])->delete();
                    AccountRole::where('accountRoleId', $staffRole['accountRoleId'])->delete();
                                        return response()->json(['success' => 'success'], 200);
                    }
            }
        }
        //Check if it is a course, then remove
        if((AccountRole::where('accountNo', $staffNo, "and")
        ->where('courseId', $unitCode)->get()) && $roleName == 'Course Coordinator'){
            //Check all the roles under this course
            $staffRoles = AccountRole::where('accountNo', $staffNo, "and")
            ->where('courseId', $unitCode)->get();
            foreach($staffRoles as $staffRole){

                if($staffRole['roleId'] == $existRole['roleId']){
                    Nomination::where('accountRoleId', $staffRole['accountRoleId'])->delete();
                    AccountRole::where('accountRoleId', $staffRole['accountRoleId'])->delete();
                                        return response()->json(['success' => 'success'], 200);
                    }
            }
        }
        return response()->json(['error' => 'Role for the unit does not exist.'], 500);
    }
    /**
     * Get all staff members under a particular line manger
     */
    public function getStaffMembers(Request $request, String $superiorNo)
    {
        $account = Account::where('accountNo', $superiorNo)->first();
        $result = [];
        $staffMembers = Account::orderBy('fName')->where('superiorNo', $superiorNo)->get();
        foreach ($staffMembers as $staffMember) {
            $staffMember['pending'] = 'No'; //initialise to random letter
            $applications = Application::where('status', 'U', "and")
            ->where('accountNo', $staffMember['accountNo'])->get();
            if($applications->isNotEmpty()){
                $staffMember['pending'] = 'Yes';
            }
            $onLeave = app(AccountController::class)->isAccountOnLeave($staffMember['accountNo']);
            if($onLeave){
                $staffMember['onLeave'] = 'Yes';
            }
            else{
                $staffMember['onLeave'] = 'No';
            }
            array_push($result, $staffMember);
        }


        if ($account->isTemporaryManager == 1) {
            /* Get Temporary subordinates */
            // Get all ManagerNominations where the superiorNo is the temporary manager
            $managerNominations = ManagerNomination::where('nomineeNo', $superiorNo)->get();

            // Iterate though manager nominations
            foreach ($managerNominations as $nomination) {
                $application = Application::where('applicationNo', $nomination->applicationNo)->first();
                
                $now = new DateTime();
                    $startDate = new DateTime($application->sDate);
                $endDate = new DateTime($application->eDate);
                
                // Process only if application is ongoing
                //   AKA status of 'Y' and StartDate >= current DateTime <= EndDate
                if ($application->status == 'Y' && ($now >= $startDate && $now <= $endDate)) {
                    $staffMember = Account::where('accountNo', $nomination->subordinateNo)->first();
                    $staffMember['pending'] = 'No'; //initialise to random letter
                    $applications = Application::where('status', 'U', "and")
                    ->where('accountNo', $staffMember['accountNo'])->get();
                    if($applications->isNotEmpty()){
                        $staffMember['pending'] = 'Yes';
                    }
                    $onLeave = app(AccountController::class)->isAccountOnLeave($staffMember['accountNo']);
                    if($onLeave){
                        $staffMember['onLeave'] = 'Yes';
                    }
                    else{
                        $staffMember['onLeave'] = 'No';
                    }
                    array_push($result, $staffMember);
                }
            }
        }

        return response()->json($result);
    }
    public function getSpecificStaffMember(Request $request, String $staffNo)
    {
        $staff = Account::where('accountNo', $staffNo)->get();
        return response()->json($staff);
    }
    public function getRolesForStaffs(Request $request, String $staffNo){

        $roleList = array();
        $staffRoles = AccountRole::where('accountNo', $staffNo)->get();
        foreach($staffRoles as $staffRole){
            $task = app(RoleController::class)->getRoleObjectFromAccountRoleId($staffRole->accountRoleId);
            array_push($roleList, $task);
        }
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

        // get regular applications if the usual accountType of the user is not staff
        $account = Account::where('accountNo', $accountNo)->first();
        if ($account->accountType == 'lmanager' || $account->accountType == 'sysadmin') {

            $regularApplications = Application::join('accounts', 'applications.accountNo', '=', 'accounts.accountNo')
            ->select('applications.*')
            ->where('accounts.superiorNo', $accountNo)
            ->whereIn('applications.status', ['Y','N','U'])
            ->get();

            foreach ($regularApplications as $app) {
                $applicant = Account::where('accountNo', $app['accountNo'])->first();
                $app['applicantName'] = "{$applicant['fName']} {$applicant['lName']}";
                $nominations = app(NominationController::class)->getNominations($app["applicationNo"]);
                $nominationsToDisplay = app(NominationController::class)->getNominationsToDisplay($app['applicationNo']);
                // check if is self nominated for all
                if ($this->isSelfNominatedAll($nominations, $accountNo)) {
                    $app['isSelfNominatedAll'] = true;
                } else {
                    $app["nominations"] = $nominations;
                    $app["nominationsToDisplay"] = $nominationsToDisplay;
                }
                array_push($managerApplications, $app);
            }
        }

        if ($account->isTemporaryManager == 1) {
            /* Get Applications from Temporary subordinates */
           // Get all ManagerNominations where the nomineeNo is the temporary manager
           $managerNominations = ManagerNomination::where('nomineeNo', $accountNo)->get();
           // Iterate though manager nominations
           foreach ($managerNominations as $nomination) {
               $application = Application::where('applicationNo', $nomination->applicationNo)
               ->where('status', 'Y')
               ->first();
               
               $now = new DateTime();
               $startDate = new DateTime($application->sDate);
               $endDate = new DateTime($application->eDate);
               // Process only if application is ongoing
               //   AKA status of 'Y' and StartDate >= current DateTime <= EndDate
               if ($application->status == 'Y' && ($now >= $startDate && $now <= $endDate)) {
                   // Get all applications from the subordinate the user is temporarily in charge of
                   $subordinateApplications = Application::where('accountNo', $nomination->subordinateNo)
                    ->whereIn('status', ['Y', 'N', 'U'])
                   ->get();

                   $subordinateApplications = Application::join('accounts', 'applications.accountNo', '=', $nomination->subordinateNo)
                   ->select('applications.*')
                   ->where('accounts.accountNo',  $nomination->subordinateNo)
                   ->where('accounts.superiorNo', $accountNo)
                   ->get();

                   foreach ($subordinateApplications as $app) {
                        $applicant = Account::where('accountNo', $app['accountNo'])->first();
                        $app['applicantName'] = "{$applicant['fName']} {$applicant['lName']}";
                        $nominations = app(NominationController::class)->getNominations($app["applicationNo"]);
                        $nominationsToDisplay = app(NominationController::class)->getNominationsToDisplay($app['applicationNo']);
                        // check if is self nominated for all
                        if ($this->isSelfNominatedAll($nominations, $accountNo)) {
                            $app['isSelfNominatedAll'] = true;
                        } else {
                            $app["nominations"] = $nominations;
                            $app["nominationsToDisplay"] = $nominationsToDisplay;
                        }
                        array_push($managerApplications, $app);
                    }
               }
            }
        }

        Log::debug($managerApplications);

        return response()->json($managerApplications);
    }
    public function getUCM(){
        $allUnits = array();
        $units = Unit::get();
        $majors = Major::get();
        $courses = Course::get();

        foreach($units as $unit)
        {
            array_push($allUnits, $unit->unitId);
        }
        foreach($majors as $major)
        {
            array_push($allUnits, $major->majorId);
        }
        foreach($courses as $course)
        {
            array_push($allUnits, $course->courseId);
        }
        return $allUnits;
    }

    public function acceptApplication(WelcomeHash $hash)
    {
        dd($hash);
    }
}
