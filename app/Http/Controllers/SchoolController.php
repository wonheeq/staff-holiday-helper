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
        } 

        // Super admin can view all schools.
        if (Account::where('accountNo', $accountNo)->where('schoolId', 1)->exists()) {
            $schools = School::get();
        }
        else {
            // Get schoolId of user
            $thisAccount = Account::where('accountNo', $accountNo)->first();

            $schools = School::where('schoolId', $thisAccount->schoolId)->get();
        }
        
        return response()->json($schools);
    }
}
