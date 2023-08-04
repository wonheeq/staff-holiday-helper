<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Nomination;
use App\Models\AccountRole;
use App\Models\Role;

class NominationController extends Controller
{
    /*
    Returns all nominations, formatted, for the given application number
    */
    public function getNominations($appNo)
    {
        $nominations = Nomination::where('applicationNo', $appNo)->get();
        $users = [];

        // iteration through each nomination
        foreach ($nominations as $nomination) {
            // get nominee id from nomination
            $nomineeNo = $nomination['nomineeNo'];

            // get name of nominee from user controller
            $nominee_user = app(UserController::class)->getUser($nomineeNo);
            $name = "{$nominee_user['fName']} {$nominee_user['lName']}";

            // Get name of role associated with the account role
            $task = $this->getRoleFromAccountRoleId($nomination['accountRoleId']);

            array_push($users, array(
                "name" => $name,
                "accountNo" => $nomineeNo,
                "task" => $task,
                "status" => $nomination['status'],
            ));
        }

        return $users;
    }   

    /*
    Returns the formatted role name of the given AccountRoleId
    */
    private function getRoleFromAccountRoleId($accountRoleId) {
        $accountRole = AccountRole::where('accountRoleId', $accountRoleId)->first();
        $role = Role::where('roleId', $accountRole['roleId'])->first();
        $roleName = $role['name'];

        // TODO: use unitId, majorId, courseId to determin real name of task after they are implemented

        $task = "UNITCODE Unit Name - {$roleName}";
        return $task;
    }
}
