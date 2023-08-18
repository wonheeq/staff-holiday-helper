<?php

namespace App\Http\Controllers;

use App\Models\AccountRole;
use App\Models\Unit;
use App\Models\Account;

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

    // Route: /api/getUnitDetails
    // Input: Request with valid unit ID in data
    // Output: Response with Unit name, ID, current UC ID and email.
    public function getUnitDetails(Request $request)
    {
        // check if correct format
        $request->validate([
            'code' => 'required|regex:/^[A-Z]{4}[0-9]{4}$/'
        ]);

        // check if unit exists, return error if it doesn't.
        $id = $request->code;
        if (!Unit::where('unitId', $id)->first()) {
            return response()->json([
                'error' => 'Unit not found'
            ], 500);
        }

        // Get the AccountNo if the current UC for the unit.
        $responsibleId = AccountRole::where([
            ['unitId', '=', $id],
            ['roleId', '=', 1],
        ])->value('accountNo');

        // Build the name of the UC
        $fName = Account::where('accountNo', $responsibleId)->value('fName');
        $lName = Account::where('accountNo', $responsibleId)->value('lName');
        $name = $fName . " " . $lName;

        // build email and get unit name
        $email = $responsibleId . "@curtin.edu.au";
        $unitName = Unit::where('unitId', $id)->value('name');

        return response()->json([
            'unitId' => $id,
            'unitName' => $unitName,
            'email' => $email,
            'name' => $name
        ]);
    }
}
