<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\User;

class ApplicationController extends Controller
{
    public function getApplications(Request $request, String $user_id)
    {
        // Check if user exists for given user id
        if (!User::find($user_id)) {
            // User does not exist, return exception
            return response()->json(['error' => 'User does not exist.'], 500);
        }

        $applications = Application::orderBy('created_at', 'desc')->where('accountNo', $user_id)->get();
        
        foreach ($applications as $val) {
            // get nominations for application
            $nominations = app(NominationController::class)->getNominations($val["id"]);
            $val["nominations"] = $nominations;
        }

        return response()->json($applications);
    }
}
