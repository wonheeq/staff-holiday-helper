<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\EmailPreference;

class EmailPreferenceController extends Controller
{

    // get the interval for a users archive email
    public function getPreference(Request $request)
    {
        $request->validate([
            'accountNo' => ['required'],
        ]);
        $accountNo = $request->accountNo;
        if(!EmailPreference::where('accountNo', $accountNo)->first())
        {
            return response()->json(['error' => 'Account does not exist.'], 500);
        }
        else
        {
            $user = EmailPreference::where('accountNo', $accountNo)->first();
            $hours = $user->hours;

            return $this->getFrequencyFromHours($hours);
        }

    }

    // Set the interval for a users archive email
    public function setPreference(Request $request)
    {
        $request->validate([
            'accountNo' => ['required'],
            'frequency' => ['required']
        ]);

        $accountNo = $request->accountNo;
        if(!EmailPreference::where('accountNo', $accountNo)->first())
        {
            return response()->json(['error' => 'Account does not exist.'], 500);
        }
        else
        {
            $user = EmailPreference::where('accountNo', $accountNo)->first();
            $user->hours = $this->getHoursFromFrequency($request->frequency);
            $user->save();
            return response(200);
        }
    }

    private function getHoursFromFrequency($frequency)
    {
        $hours = 0;
        switch ($frequency) {
            case "Hourly":
                $hours = 1;
            break;

            case "Twice a day":
                $hours = 12;
            break;

            case "Daily":
                $hours = 24;
            break;

            case "Every 2 days":
                $hours = 48;
            break;

            case "Every 3 days":
                $hours = 72;
            break;

            case "Every 4 days":
                $hours = 96;
            break;

            case "Every 5 days":
                $hours = 120;
            break;

            case "Every 6 days":
                $hours = 144;
            break;

            case "Once a week":
                $hours = 168;
            break;

            default:
                $hours = 24;
        }

        return $hours;
    }

    private function getFrequencyFromHours($hours)
    {
        $frequency = "";
        switch ($hours) {
            case 1:
                $frequency = "Hourly";
            break;

            case 12:
                $frequency = "Twice a day";
            break;

            case 24:
                $frequency = "Daily";
            break;

            case 48:
                $frequency = "Every 2 days";
            break;

            case 72:
                $frequency = "Every 3 days";
            break;

            case 96:
                $frequency = "Every 4 days";
            break;

            case 120:
                $frequency = "Every 5 days";
            break;

            case 144:
                $frequency = "Every 6 days";
            break;

            case 168:
                $frequency = "Once a week";
            break;

            default:
                $frequency = "Daily";
        }

        return $frequency;
    }
}
