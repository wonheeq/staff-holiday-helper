<?php

namespace App\Http\Controllers;

use App\Models\School;


use Illuminate\Http\Request;

class SchoolController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {       
        return School::all(); 
    }
}
