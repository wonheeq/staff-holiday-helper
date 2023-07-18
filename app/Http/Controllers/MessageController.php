<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;

class MessageController extends Controller
{
    public function get(Request $request)
    {
        $messages = Message::orderBy('created_at', 'desc')->get();
        
        foreach ($messages as $val) {
            $sender = app(UserController::class)->getUser($val["sender_id"]);
            $val["sender_name"] = $sender["name"];
        }

        return response()->json($messages);
    }
}
