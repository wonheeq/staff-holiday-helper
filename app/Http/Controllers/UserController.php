<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;

class UserController extends Controller
{
    public function getUser($accountNo)
    {
        $user = Account::where('accountNo', $accountNo)->get()[0];
        return $user;
    }   
}