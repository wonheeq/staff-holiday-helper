<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;

class BookingController extends Controller
{
    /*
    Returns a list of all account numbers in the system that belong to the same school
    as the account for the given account number.
    This list excludes the given account number.
    This list has "Self Nomination" at the front of the array
    */
    public function getBookingOptions(Request $request, String $accountNo) {
        // Todo: Check for schoolId after it gets implemented 

        $users = Account::where("accountNo", "!=", $accountNo)->get();
        $data = ["Self Nomination"];

        foreach ($users as $user) {
            array_push($data, "({$user['accountNo']}) {$user['fName']} {$user['lName']}");
        }

        return response()->json($data);
    }
}
