<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class BookingController extends Controller
{
    public function getBookingOptions(Request $request, String $user_id) {
        $users = User::where("id", "!=", $user_id)->get();

        $data = ["Self Nomination"];

        foreach ($users as $user) {
            array_push($data, "({$user['id']}) {$user['name']}");
        }

        return response()->json($data);
    }
}
