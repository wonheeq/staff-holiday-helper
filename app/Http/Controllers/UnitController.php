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

    public function getUnitDetails(Request $request)
    {
        $request->validate([
            'code' => 'required|regex:/^[A-Z]{4}[0-9]{4}$/'
        ]);
        // check if matches unit code format
        // check if real unit
        // get details and send back in json
    }
}
