<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;

class MessageController extends Controller
{
    public function getMessages(Request $request, String $user_id)
    {
        // Check if user exists for given user id
        if (!User::find($user_id)) {
            // User does not exist, return exception
            return response()->json(['error' => 'User does not exist.'], 500);
        }

        $messages = Message::orderBy('created_at', 'desc')
            ->where('receiver_id', $user_id)
            ->get();
        
        foreach ($messages as $val) {
            $sender = app(UserController::class)->getUser($val["sender_id"]);
            $val["sender_name"] = $sender["name"];
        }

        return response()->json($messages);
    }
}
