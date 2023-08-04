<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;

class BookingController extends Controller
{
    public function getBookingOptions(Request $request, String $accountNo) {
        $users = Account::where("accountNo", "!=", $accountNo)->get();

        $data = ["Self Nomination"];

        foreach ($users as $user) {
            array_push($data, "({$user['accountNo']}) {$user['fName']} {$user['lName']}");
        }

        return response()->json($data);
    }
}
