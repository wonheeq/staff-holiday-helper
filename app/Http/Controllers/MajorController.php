<?php

namespace App\Http\Controllers;

use App\Models\Major;

use Illuminate\Http\Request;

class MajorController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {       
        return Major::all(); 
    }
}
