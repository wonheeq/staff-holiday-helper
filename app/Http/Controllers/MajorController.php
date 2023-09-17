<?php

namespace App\Http\Controllers;

use App\Models\Major;
use App\Models\Account;

use Illuminate\Http\Request;

class MajorController extends Controller
{
    /*
    Returns all Majors
     */
    public function getAllMajors(Request $request, String $accountNo)
    {
        // Check if user exists for given accountNo
        if (!Account::where('accountNo', $accountNo)->first()) {
            // User does not exist, return exception
            return response()->json(['error' => 'Account does not exist.'], 500);
        } else {
            $majors = Major::get();
            return response()->json($majors);
        }
    }
}
