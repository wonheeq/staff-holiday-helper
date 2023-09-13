<?php

namespace App\Http\Controllers;

use App\Models\Account;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class DatabaseController extends Controller
{
    /*
    Works out what table a new entry should be added to and calls a method to create the new row
     */
    public function addEntry(Request $request, String $accountNo)
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

            $data = $request->all();

            // Use 'fields' to work out which model the entry applies to.
            switch ($data['fields']) {
                case 'accountFields':
                    $response = $this->addAccount($data['newEntry']);
                    break;
                default:
                    return response()->json(['error' => 'Could not determine db table'], 500);
            }
  
            return $response;
        }  
    }

    private function addAccount(array $attributes) {
        //Log::info($attributes);
        //Log::info($attributes[1]['db_name']);

        // Check that un-restricted attributes are valid

        // checking new primary key
        if (Account::where('accountNo', $attributes[0])->exists())
        {
            return response()->json(['error' => 'Account ID already in use.'], 500);
        }

        // accountNo
        if (strlen($attributes[0]) != 7 || !preg_match("/\A[0-9]{6}[a-z]{1}/", $attributes[0])) {
            return response()->json(['error' => 'Account Number needs syntax<br />of 6 numbers followed by<br />lowercase letter with no spaces'], 500);
        }


        $account = Account::create([
            'accountNo' => $attributes[0],
            'accountType' =>  $attributes[1]['db_name'],
            'lname' => $attributes[2],
            'fname' => $attributes[3],
            'password' => Hash::make(fake()->regexify('[A-Za-z0-9#@$%^&*]{10,15}')), // Password created randomly
            'superiorNo' => $attributes[5]['accountNo'],
            'schoolId' => $attributes[4]['schoolId'],
        ]);

        return response()->json(['success' => 'success'], 200);
    }
}
