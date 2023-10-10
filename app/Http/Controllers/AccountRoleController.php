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

        // Super admin can view all accounts.
        if (Account::where('accountNo', $accountNo)->where('schoolId', 1)->exists()) {
            $accountRoles = AccountRole::get();
        }
        else {
            // Get schoolId of user
            $thisAccount = Account::where('accountNo', $accountNo)->first();

            //$accountRoles = AccountRole::where('schoolId', $thisAccount->schoolId)->get();   
            $accountRoles = AccountRole::join('accounts', 'account_roles.accountNo', '=', 'accounts.accountNo')->select('account_roles.*')->where('accounts.schoolId', $thisAccount->schoolId)->orWhere('account_roles.schoolId', $thisAccount->schoolId)->get();           
        }
        
        return response()->json($accountRoles); 
    }
}
