<?php

namespace App\Http\Controllers;

use App\Models\AccountRole;
use App\Models\Unit;
use App\Models\Account;
use App\Models\Nomination;
use App\Models\Application;
use Illuminate\Http\Request;
use SplFixedArray;
use DateTime;

class UnitController extends Controller
{
    /*
    Returns all Units
     */
    public function getAllUnits(Request $request, String $accountNo)
    {
        // Check if user exists for given accountNo
        if (!Account::where('accountNo', $accountNo)->first()) {
            // User does not exist, return exception
            return response()->json(['error' => 'Account does not exist.'], 500);
        } else {
            $units = Unit::get();
            return response()->json($units);
        }
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

        // get details of Unit Coordinator, Major Coordinator, Course Coordinator for the unit
        $ucDetails = $this->getAccountForUnitRole($id, 1);


        // check if there is a substitue / get the current details for each of them
        $currentUc = '';
        if ($ucDetails != null) {
            $currentUc = $this->checkForSub($ucDetails->accountRoleId, $ucDetails->accountNo);
        }

        // get the current lecturers for the unit
        $currentLecturers = $this->getActiveLecturersForUnit($id);

        $unitName = Unit::where('unitId', $id)->value('name');

        return response()->json([
            'unitId' => $id,
            'unitName' => $unitName,
            'unitCoord' => $currentUc,
            'lecturers' => $currentLecturers
        ]);
    }


    // Helper function for getUnitDetails()
    // Imports: accountRoleId and accountNo to check if there is a substiute for
    // Exports: array containing the accountRoleId and accountNo of
    //          currently responsible staff member.
    private function checkForSub($accountRoleId, $accountNo): array
    {
        // set date time for timestamp comparisons
        // date_default_timezone_set('Australia/Perth');
        // $timezone = date_default_timezone_get();
        $time = new DateTime('NOW');
        // attempt to get an approved, active leave period for the given
        // staff member
        $applicationNo = Application::where([
            ['accountNo', '=', $accountNo], ['status', '=', 'Y'],
            ['sDate', '<=', $time], ['eDate', '>=', $time]
        ])->value('applicationNo');

        // if there is not one, applicationNo is null and block not entered
        $nomineeAccountNo = null;
        if ($applicationNo != null) {
            // attempt to get the account number of the nominee that has accepted
            // responsiblity of this role, if there is one
            $nomineeAccountNo = Nomination::where([
                ['applicationNo', '=', $applicationNo],
                ['status', '=', 'Y'],
                ['accountRoleId', '=', $accountRoleId],
            ])->value('nomineeNo');
        }

        // if there wasn't, nomineeAccountNo is null and accountNo is not updated
        if ($nomineeAccountNo != null) {
            $accountNo = $nomineeAccountNo;
        }

        // get and build name and email for currently responsible staff member.
        $nameVals = Account::where('accountNo', $accountNo)
            ->first(['fName', 'lName']);
        $name = $nameVals->fName . " " . $nameVals->lName;
        $email = $accountNo . "@curtin.edu.au";

        return array($email, $name);
    }


    // Helper function for getUnitDetails()
    // Imports: unit ID
    // Exports: array of accountNo and accountRoleId for all staff currently
    //          responsible for lectures in the given unit
    private function getActiveLecturersForUnit($unitId): SplFixedArray
    {
        // get all of the accountRoleIds and accountNos as an array
        $acccountDetailsArr = AccountRole::where([
            ['unitId', '=', $unitId],
            ['roleId', '=', 4],
        ])->get(['accountRoleId', 'accountNo'])->toArray();

        // count the elements and create an output array
        $count = count($acccountDetailsArr);
        $results = new SplFixedArray($count);

        // loop through each lecturer and check if they have an active substitute
        for ($i = 0; $i <= $count - 1; $i++) {
            $lecturer = $acccountDetailsArr[$i];
            $currDetails = $this->checkForSub($lecturer["accountRoleId"], $lecturer["accountNo"]);
            $results[$i] = $currDetails;
        }

        return $results;
    }

    // Helper function for getUnitDetails()
    // Imports: Unit ID, Role ID (UC, MC, CC, lecturer)
    // Returns: accountNo and accountRoleId for the given unit and role
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
