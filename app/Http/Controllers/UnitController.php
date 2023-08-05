<?php

namespace App\Http\Controllers;

use App\Models\Unit;


use Illuminate\Http\Request;

class UnitController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {       
        return Unit::all(); 
    }
}
