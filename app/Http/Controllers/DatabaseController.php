<?php

namespace App\Http\Controllers;



use Illuminate\Http\Request;

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
            //$data['fields']

            // Work out how $data works and then retrieve it.

            
            response()->json(['success' => 'success'], 200);
        }  
    }
}
