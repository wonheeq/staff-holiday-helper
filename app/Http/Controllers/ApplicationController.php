<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\User;

class ApplicationController extends Controller
{
    public function get(Request $request)
    {
        // temporary user
        $user_id = User::inRandomOrder()->limit(1)->get()[0]['id'];
        $applications = Application::orderBy('created_at', 'desc')->where('accountNo', $user_id)->get();
        
        foreach ($applications as $val) {
            // get nominations for application
            $nominations = app(NominationController::class)->getNominations($val["id"]);
            $val["nominations"] = $nominations;
        }

        return response()->json($applications);
    }
}
