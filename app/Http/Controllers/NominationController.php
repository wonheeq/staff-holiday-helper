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
            $task = app(RoleController::class)->getRoleFromAccountRoleId($nomination['accountRoleId']);

            array_push($users, array(
                "name" => $name,
                "accountNo" => $nomineeNo,
                "task" => $task,
                "status" => $nomination['status'],
            ));
        }

        return $users;
    }
}
