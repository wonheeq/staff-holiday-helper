<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AccountRole;
use App\Models\Account;
use App\Models\Role;

class RoleController extends Controller
{
     /*
    Returns the formatted role name of the given AccountRoleId
    */
    public function getRoleFromAccountRoleId($accountRoleId) {
        $accountRole = AccountRole::where('accountRoleId', $accountRoleId)->first();
        $role = Role::where('roleId', $accountRole['roleId'])->first();
        $roleName = $role['name'];

        // TODO: use unitId, majorId, courseId to determin real name of task after they are implemented

        $task = "UNITCODE Unit Name - {$roleName}";
        return $task;
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
        }
        else {
            // Verify that the account is a system admin account
            if (!Account::where('accountNo', $accountNo)->where('accountType', 'sysadmin')->first()) {
                // User is not a system admin, deny access to full table
                return response()->json(['error' => 'User not authorized for request.'], 500);
            }

            $roles = Role::get();
            return response()->json($roles);  
        }  
    }
}
