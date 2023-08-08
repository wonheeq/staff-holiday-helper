<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\Account;
use App\Models\Application;
use App\Models\Nomination;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    /*
    Returns all messages, formatted,  for the given account
    */
    public function getMessages(Request $request, String $accountNo)
    {
        // Check if Account exists for given accountNo
        if (!Account::where('accountNo', $accountNo)->first()) {
            // Account does not exist, return exception
            return response()->json(['error' => 'Account does not exist.'], 500);
        }

        $messages = Message::orderBy('created_at', 'asc')
            ->where('receiverNo', $accountNo)
            ->get();
        
        
        foreach ($messages as $message) {
            // Add in sendername of each message
            if ($message['senderNo'] != null) {
                // if sender is not null, then sender is a user
                $sender = app(UserController::class)->getUser($message["senderNo"]);
                $message["senderName"] = "{$sender['fName']} {$sender['lName']}";
            }
            else {
                // senderNo is null, therefore sender is the system
                $message["senderName"] = "SYSTEM";
            }


            // get nominations if subject is Substitution Request
            if ($message['subject'] == "Substitution Request") {
                // applicationNo SHOULD exist
                // Get all nominations for the application where the nomineeNo == accountNo

                $nominations = Nomination::where('applicationNo', $message['applicationNo'], 'and')
                                            ->where('nomineeNo', $accountNo)->get();

                $count = count($nominations);
                Log::debug("NUM: {$count}");
                if ($count > 1) {
                    // add isNominatedMultiple flag to message data
                    $message["isNominatedMultiple"] = true;
                }
            }
        }

        return response()->json($messages);
    }
}
