<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Account;

use Illuminate\Http\Request;

class SchoolController extends Controller
{
    /*
    Returns all Schools
     */
    public function getAllSchools(Request $request, String $accountNo)
    {
        // Check if user exists for given accountNo
        if (!Account::where('accountNo', $accountNo)->first()) {
            // User does not exist, return exception
            return response()->json(['error' => 'Account does not exist.'], 500);
        } else {
            $schools = School::get();
            return response()->json($schools);
        }
    }
}
