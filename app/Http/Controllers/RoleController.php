<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AccountRole;
use App\Models\Account;
use App\Models\Role;
use App\Models\Unit;
use App\Models\Major;
use App\Models\Course;

class RoleController extends Controller
{
    /*
    Returns the formatted role name of the given AccountRoleId
    */
    public function getRoleFromAccountRoleId($accountRoleId)
    {
        $accountRole = AccountRole::where('accountRoleId', $accountRoleId)->first();
        if ($accountRole == null) {
            return "INVALID";
        }

        $role = Role::where('roleId', $accountRole['roleId'])->first();
        $roleName = $role['name'];

        $unitId = $accountRole->unitId;
        $majorId = $accountRole->majorId;
        $courseId = $accountRole->courseId;

        // Check if role is for major coordinator
        if ($roleName == "Major Coordinator") {
            // Get major name
            $major = Major::where('majorId', $majorId)->first();
            $majorName = $major->name;
            return "{$majorId} {$majorName} - {$roleName}";
        }
        // Check if role is for course coordinator
        else if ($roleName == "Course Coordinator") {
            // Get course name
            $course = Course::where('courseId', $courseId)->first();
            $courseName = $course->name;

            return "{$courseId} {$courseName} - {$roleName}";
        }

        // Default to unit name
        // Get unit name
        $unit = Unit::where('unitId', $unitId)->first();
        $unitName = $unit->name;

        return "{$unitId} {$unitName} - {$roleName}";
    }

    /*
    Returns all Roles
     */
    public function getAllRoles(Request $request, String $accountNo)
    {
        // Check if user exists for given accountNo
        if (!Account::where('accountNo', $accountNo)->first()) {
            // User does not exist, return exception
            return response()->json(['error' => 'Account does not exist.'], 500);
        } else {
            $roles = Role::get();
            return response()->json($roles);
        }
    }
}
