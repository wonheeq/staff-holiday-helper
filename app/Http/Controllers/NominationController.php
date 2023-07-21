<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Nomination;

class NominationController extends Controller
{
    public function getNominations($appNo)
    {
        $nominations = Nomination::where('applicationNo', $appNo)->get();
        $users = [];

        // iteration through each nomination
        foreach ($nominations as $nomination) {
            // get nominee id from nomination
            $nominee = $nomination['nominee'];

            // get name of nominee from user controller
            $nominee_user = app(UserController::class)->getUser($nominee);
            $name = $nominee_user['name'];

            array_push($users, array(
                "name" => $name,
                "user_id" => $nominee,
                "task" => $nomination['task'],
            ));
        }

        return $users;
    }   
}
