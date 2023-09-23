<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\Nomination;
use App\Models\Application;
use DateTime;
use DateTimeZone;

use Illuminate\Support\Facades\Log;


class CalendarController extends Controller
{
    /*
    Returns the calendar data formatted for use with VCalendar
    Includes calendar data for all applications of the user
    Includes calendar data for all accepted applications where a
        the user has accepted to be a substitute
    */
    public function getCalendarData(Request $request, String $accountNo) {
        // Check if user exists for given user id
        if (!Account::where('accountNo', $accountNo)->first()) {
            // User does not exist, return exception
            return response()->json(['error' => 'Account does not exist.'], 500);
        }

        $data = array();
        
        $appData = $this->handleApplications($accountNo);
        $nomData = $this->handleSubstitutions($accountNo);

        $data = $this->addToArray($data, $appData);
        $data = $this->addToArray($data, $nomData);


        return response()->json($data);
    }

    private function addToArray(array $arr1, array $arr2) {
        foreach ($arr2 as $e) {
            array_push($arr1, $e);
        }
        return $arr1;
    }

    /*
    Returns formatted calendar data for approved applications that the user is a substitute for
    */
    private function handleSubstitutions(String $accountNo) {
        $data = array();

        // Get all nominations where the user is a nominee
        $nominations = Nomination::where("nomineeNo", "=", $accountNo)->get();

        // Iterate through each nomination
        foreach ($nominations as $nomination) {
            // Grab application details of accepted nominations
            // Only generate calendar data for accepted nominations
            if ($nomination['status'] == 'Y') {
                $application = Application::where("applicationNo", "=", $nomination['applicationNo'])->first();
                
                if ($application != null && $application['status'] == 'Y') {
                    // Get details of accepted application
                    $startDate = $application['sDate'];
                    $endDate = $application['eDate'];
                    $applicationMaker = Account::where("accountNo", "=", $application['accountNo'])->first();
                    $task = app(RoleController::class)->getRoleFromAccountRoleId($nomination['accountRoleId']);

                    $content = "{$task} for {$applicationMaker['fName']} {$applicationMaker['lName']} ({$startDate} - {$endDate})";

                    $rangeData = array(
                        'highlight' => 'purple',
                        'dates' => [
                            [
                                $application['sDate'],
                                $application['eDate']
                            ]
                        ],
                        'popover' => [
                            'label' => $content,
                        ]
                    );
                    array_push($data, $rangeData);
                }
            }
        }

        return $data;
    }

    /*
    Returns formatted calendar data for all applications of the user
    */
    private function handleApplications(String $accountNo) {
        $data = array();
        // Get all applications of user
        $applications = Application::where("accountNo", "=" , $accountNo)->get();

        // Iterate through each application and add data to data array
        foreach ($applications as $app) {
            switch ($app['status']) {
                case 'Y': {
                    // Application is approved
                    $rangeData = array(
                        'highlight' => 'green',
                        'dates' => [
                            [
                                $app['sDate'],
                                $app['eDate']
                            ]
                        ],
                        'popover' => [
                            'label' => 'Approved',
                        ]
                    );
                    array_push($data, $rangeData);
                    break;
                }
                case 'N': {
                    // Application is rejected
                    $manager = Account::where('accountNo', $app['processedBy'])->first();
                    $rejector = $manager != null ? "{$manager['fName']} {$manager['lName']}" : 'System';
                    $rangeData = array(
                        'highlight' => 'red',
                        'dates' => [
                            [
                                $app['sDate'],
                                $app['eDate']
                            ]
                        ],
                        'popover' => [
                            'label' => "Rejected by {$rejector}: {$app['rejectReason']}",
                        ]
                    );
                    array_push($data, $rangeData);
                    break;
                }
                case 'U': {
                    // Application is Undecided status
                    $rangeData = array(
                        'highlight' => 'blue',
                        'dates' => [
                            [
                                $app['sDate'],
                                $app['eDate']
                            ]
                        ],
                        'popover' => [
                            'label' => 'Awaiting decision from line manager',
                        ]
                    );
                    array_push($data, $rangeData);
                    break;
                }
                case 'P': {
                    // Application is Pending status
                    
                    // Get nominations for application
                    $nominations = Nomination::where('applicationNo', $app['applicationNo'])->get();

                    $nomineesResponded = 0;
                    $nomineesTotal = 0;

                    foreach ($nominations as $nomination) {
                        if ($nomination['status'] != 'U') {
                            $nomineesResponded++;
                        }
                        $nomineesTotal++;
                    }

                    $rangeData = array(
                        'highlight' => 'orange',
                        'dates' => [
                            [
                                $app['sDate'],
                                $app['eDate']
                            ]
                        ],
                        'popover' => [
                            'label' => "Pending Nominee Response: {$nomineesResponded}/{$nomineesTotal}",
                        ]
                    );
                    array_push($data, $rangeData);
                    break;
                }
                default: {
                    // cancelled status -- ignore silently
                    break;
                }
            }
        }
        return $data;
    }
}
