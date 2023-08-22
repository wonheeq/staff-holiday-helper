<?php

namespace App\Http\Controllers;

use App\Models\AccountRole;
use App\Models\Account;

use Illuminate\Http\Request;

class AccountRoleController extends Controller
{
    /*
    Returns all AccountRoles
     */
    public function getAllAccountRoles(Request $request, String $accountNo)
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

            $accountRoles = AccountRole::get();
            return response()->json($accountRoles);  
        }  
    }
}
