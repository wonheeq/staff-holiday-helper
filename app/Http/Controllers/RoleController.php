<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
}
