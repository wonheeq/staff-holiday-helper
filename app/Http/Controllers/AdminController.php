<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\ReminderTimeframe;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /*
    Returns the reminder timeframe (string) for the user's school
    */
    public function getReminderTimeframe(Request $request, string $accountNo) {
        // Check if user exists for given accountNo
        if (!Account::where('accountNo', $accountNo)->first()) {
            // User does not exist, return exception
            return response()->json(['error' => 'Account does not exist.'], 500);
        }

        $account = Account::where('accountNo', $accountNo)->first();

        return ReminderTimeframe::where('schoolId', $account->schoolId)->first()->timeframe;
    }

    /*
    Sets the reminder timeframe for the user's school
    */
    public function setReminderTimeframe(Request $request) {
        $data = $request->all();
        $timeframe = $data['timeframe'];
        $accountNo = $data['accountNo'];

        // Check if user exists for given accountNo
        if (!Account::where('accountNo', $accountNo)->first()) {
            // User does not exist, return exception
            return response()->json(['error' => 'Account does not exist.'], 500);
        }

        // Verify that the account is a system admin account
        if (!Account::where('accountNo', $accountNo)->where('accountType', 'sysadmin')->first()) {
            // User is not a system admin, deny access to full table
            return response()->json(['error' => 'User not authorized for request.'], 500);
        }

        $account = Account::where('accountNo', $accountNo)->first();

        // Check that the ReminderTimeframe for the school exists
        if (!ReminderTimeframe::where('schoolId', $account->schoolId)->first()) {
            return response()->json(['error' => 'ReminderTimeframe/School does not exist.'], 500);
        }

        // Check that the given timeframe is valid
        $validTimeframes = [
            "1 day",
            "2 days",
            "3 days",
            "4 days",
            "5 days",
            "6 days",
            "1 week",
        ];
        if (!in_array($timeframe, $validTimeframes)) {
            return response()->json(['error' => 'Invalid timeframe'], 500);
        }

        ReminderTimeframe::where('schoolId', $account->schoolId)
        ->update([
            'timeframe' => $timeframe
        ]);

        return response()->json(['success'], 200);
    }
}
