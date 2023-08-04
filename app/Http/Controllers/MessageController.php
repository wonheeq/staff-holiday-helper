<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\Account;

use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
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
        
        foreach ($messages as $val) {
            $sender = app(UserController::class)->getUser($val["senderNo"]);
            $val["senderName"] = "{$sender['fName']} {$sender['lName']}";
        }

        return response()->json($messages);
    }
}
