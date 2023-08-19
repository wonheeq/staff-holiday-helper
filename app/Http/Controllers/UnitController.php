<?php

namespace App\Http\Controllers;

use App\Models\AccountRole;
use App\Models\Unit;
use App\Models\Account;
use App\Models\Nomination;
use App\Models\Application;
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
        // TODO:
        // check if there exists a nomination with matching account role id first
        // else get the normal staff member.
        // get all staff connected to that role?

        /*
        Things to get:
        - unit name/id
        - Active UC name/email
        - Active CC name/email
        - Active MC name/email
        - Active Lecturers names/emails

        */

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

        date_default_timezone_set('Australia/Perth');
        $this->getActiveUC($id);
        //call each helper function
        //get result from each
        //format json
        //return response

    }

    // helper function for getUnitDetails()
    // gets the name and email of the active unit coordinator for a given unit.
    private function getActiveUC(String $unitId): array
    {
        // for the given unit, get the role ID and account number of responsible staff
        $colVals = AccountRole::where([
            ['unitId', '=', $unitId],
            ['roleId', '=', 1]
        ])->first(['accountRoleId', 'accountNo']);
        $accountRoleId = $colVals->accountRoleId;
        $accountNo = $colVals->accountNo;

        // if there is one, get the ID of an active, accepted leave application
        // for this staff member
        $timezone = date_default_timezone_get();
        $applicationNo = Application::where([
            ['accountNo', '=', $accountNo], ['status', '=', 'Y'],
            ['sDate', '<=', $timezone], ['eDate', '>=', $timezone]
        ])->value('applicationNo');

        // if there was an application
        if ($applicationNo != null) {

            // if there is an accepted nomination for that application,
            // for the UC accountRoleId, get the nominee account number.
            $nomineeAccountNo = Nomination::where([
                ['applicationNo', '=', $applicationNo],
                ['status', '=', 'Y'],
                ['accountRoleId', '=', $accountRoleId],
            ])->value('nomineeNo');
        }

        // update accountNo if there was an active substitute for the role
        if ($nomineeAccountNo != null) {
            $accountNo = $nomineeAccountNo;
        }

        // get and build name
        $nameVals = Account::where('accountNo', $accountNo)
            ->first(['fName', 'lName']);
        $name = $nameVals->fName . $nameVals->lName;

        $email = $accountNo . "@curtin.edu.au";

        return array($email, $name);
    }

    // private function getActiveCC(): array
    // {
    // }

    // private function getActiveMC(): array
    // {
    // }

    // private function getActiveLecturers(): array
    // {
    // }
}
