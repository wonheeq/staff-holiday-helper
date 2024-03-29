<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Course;
use App\Models\Major;
use App\Models\Unit;
use App\Models\Account;
//use App\Models\Nomination;
//use App\Models\Application;
use App\Models\AccountRole;
use App\Models\Role;
//use App\Models\Message;
use DB;

use Illuminate\Http\Request;

class ForeignKeyController extends Controller
{
    /**
     * Returns all foreign keys and their names for admin - add data, only primary keys and attributes used as foreign keys are sent.
     */
    public function getAllFKs(Request $request, String $accountNo)
    {
        // Check if user exists for given accountNo
        if (!Account::where('accountNo', $accountNo)->first()) {
            // User does not exist, return exception
            return response()->json(['error' => 'Account does not exist.'], 500);
        }
        else {
            // Verify that the account is a system admin account
            if (!Account::where('accountNo',  $accountNo)->where('accountType', 'sysadmin')->first()) {
                // User is not a system admin, deny access to full table
                return response()->json(['error' => 'User not authorized for request.'], 500);
            }

            // Getting attributes needed as foreign keys, identifiers or user friendly data
            //$accountFK = Account::get();
            //$accountFK->makeHidden(['accountType','schoolId','created_at','updated_at']);

            //$accountRoleFK = AccountRole::get();
            //$accountRoleFK->makeHidden(['accountNo','roleId','unitId','majorId','courseId','schoolId','created_at','updated_at']);

            //$applicationFK = Application::get();
            //$applicationFK->makeHidden(['accountNo','sDate','eDate','status','processedBy','rejectReason','created_at','updated_at']);

            $roleFK = Role::get();
            $roleFK->makeHidden(['created_at','updated_at']);

            $unitFK = Unit::select("unitId",DB::raw("CONCAT(name, ' (', unitId, ')') AS disName"))->get();
            //$unitFK->makeHidden(['created_at','updated_at']);

            $majorFK = Major::select("majorId",DB::raw("CONCAT(name, ' (', majorId, ')') AS disName"))->get();
            //$majorFK->makeHidden(['created_at','updated_at']);

            $courseFK = Course::select("courseId",DB::raw("CONCAT(name, ' (', courseId, ')') AS disName"))->get();
            //$courseFK->makeHidden(['created_at','updated_at']);

            $userAccount = Account::where('accountNo', $accountNo)->first();

            // Super admin can view all schools.
            if ($userAccount->schoolId == 1) {
                $schoolFK = School::get();
            }
            else {
                $schoolFK = School::where('schoolId', $userAccount->schoolId)->get();
            }

            $schoolFK->makeHidden(['created_at','updated_at']);               

            $result = array($roleFK, $unitFK, $majorFK, $courseFK, $schoolFK);  

            return response()->json($result);
        }  
    }
}