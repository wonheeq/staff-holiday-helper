<?php

namespace App\Http\Controllers;

use App\Models\AccountRole;
use App\Models\Unit;
use App\Models\Account;
use App\Models\Nomination;
use App\Models\Application;
use Illuminate\Http\Request;
use SplFixedArray;

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
        // $name = $nameVals->fName . $nameVals->lName;
        // $email = $accountNo . "@curtin.edu.au";




        $ucDetails = $this->getAccountForUnitRole($id, 1);
        $currentUc = $this->checkForSub($ucDetails->accountRoleId, $ucDetails->accountNo);

        $mcDetails = $this->getAccountForUnitRole($id, 2);
        $currentMc = $this->checkForSub($mcDetails->accountRoleId, $mcDetails->accountNo);

        $ccDetails = $this->getAccountForUnitRole($id, 3);
        $currentCc = $this->checkForSub($ccDetails->accountRoleId, $ccDetails->accountNo);

        $currentLecturers = $this->getActiveLecturersForUnit($id);

        dd($currentUc, $currentCc, $currentMc, $currentLecturers);
    }

    private function checkForSub($accountRoleId, $accountNo): array
    {
        // if there is one, get the ID of an active, accepted leave application
        // for this staff member
        date_default_timezone_set('Australia/Perth');
        $timezone = date_default_timezone_get();
        $applicationNo = Application::where([
            ['accountNo', '=', $accountNo], ['status', '=', 'Y'],
            ['sDate', '<=', $timezone], ['eDate', '>=', $timezone]
        ])->value('applicationNo');

        $nomineeAccountNo = null;
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

        // get and build name and email
        $nameVals = Account::where('accountNo', $accountNo)
            ->first(['fName', 'lName']);
        $name = $nameVals->fName . " " . $nameVals->lName;
        $email = $accountNo . "@curtin.edu.au";

        return array($email, $name);
    }

    private function getActiveLecturersForUnit($unitId): SplFixedArray
    {
        $acccountDetailsArr = AccountRole::where([
            ['unitId', '=', $unitId],
            ['roleId', '=', 4],
        ])->get(['accountRoleId', 'accountNo'])->toArray();

        $count = count($acccountDetailsArr);
        $results = new SplFixedArray($count);
        // dd($acccountDetailsArr);
        for ($i = 0; $i <= $count - 1; $i++) {
            $lecturer = $acccountDetailsArr[$i];
            $currDetails = $this->checkForSub($lecturer["accountRoleId"], $lecturer["accountNo"]);
            $results[$i] = $currDetails;
        }

        return $results;
    }

    private function getAccountForUnitRole($unitId, $roleId)
    {
        // for the given unit, get the role ID and account number of responsible staff
        $colVals = AccountRole::where([
            ['unitId', '=', $unitId],
            ['roleId', '=', $roleId]
        ])->first(['accountRoleId', 'accountNo']);

        return $colVals;
    }
}
