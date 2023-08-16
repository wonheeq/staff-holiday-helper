<?php

namespace App\Http\Controllers;

use App\Models\Account;

use Illuminate\Http\Request;
define("DEFAULT_ADMIN_ACCOUNT_NO", "000002L");

class AccountController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {       
        //$accounts = Account::get();
        //return response()->json($accounts);

        return Account::all(); 
    }

    public function getDefaultAdmin() {
        return Account::where('accountNo', DEFAULT_ADMIN_ACCOUNT_NO)->first();
    }

    public function getCurrentLineManager(String $accountNo) {
        try {
            // Attempt to get user
            $user = Account::where('accountNo', $accountNo)->first();
            $superiorNo = $user->superiorNo;

            if ($superiorNo == null) {
                throw new ErrorException();
            }
            return Account::where('accountNo', $superiorNo)->first();
        }
        catch (Exception $e) {
            // Error occurred - return default admin user
            return $this->getDefaultAdmin();
        }
    }
}
