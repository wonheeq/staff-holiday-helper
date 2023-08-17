<?php

namespace App\Http\Controllers;

use App\Models\AccountRole;
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

        $id = $request->code;

        // check if unit exists, return error if it doesn't.
        if (!Unit::where('unitId', $id)->first()) {
            return response()->json([
                'error' => 'Unit not found'
            ], 500);
        }

        $responsibleId = AccountRole::where([
            ['unitId', '=', $id],
            ['roleId', '=', 1],
        ])->value('accountNo');
    }
}
