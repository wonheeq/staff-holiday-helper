<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\EmailPreference;

class EmailPreferenceController extends Controller
{

    // Set the interval for a users archive email
    public function setPreference(Request $request)
    {
        dd($request);
        $request->validate([
            'accountNo' => ['required'],
            'hours' => ['required']
        ]);
        $accountNo = $request->accountNo;
        $frequency = $request->hours;
        if(!EmailPreference::where('accountNo', $accountNo)->first())
        {
            return response()->json(['error' => 'Account does not exist.'], 500);
        }
        else
        {
            $user = EmailPreference::where('accountNo', $accountNo)->first();
            $user->hours = $frequency;
            $user->save();
            return response(200);
        }
    }
}
