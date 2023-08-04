<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;

class UserController extends Controller
{
    /*
    Returns the account for the given accountNumber
    */
    public function getUser($accountNo)
    {
        $user = Account::where('accountNo', $accountNo)->first();
        return $user;
    }   
}