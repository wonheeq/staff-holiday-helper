<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\Nomination;
use App\Models\Application;
use \DateTime;

use Illuminate\Support\Facades\Log;


class CalendarController extends Controller
{
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

    private function handleSubstitutions(String $accountNo) {
        $data = array();

        $nominations = Nomination::where("nomineeNo", "=", $accountNo)->get();

        // Iterate through each nomination
        foreach ($nominations as $nomination) {
            // Grab application details of accepted nominations
            if ($nomination['status'] == 'Y') {
                $application = Application::where("applicationNo", "=", $nomination['applicationNo'])->first();
                
                if ($application != null) {
                    $startDate = date_format(new DateTime($application['start']), "d/m/Y H:i");
                    $endDate = date_format(new DateTime($application['end']), "d/m/Y H:i");
                    $applicationMaker = Account::where("accountNo", "=", $application['accountNo'])->first()['name'];
                    $task = $nomination['task'];

                    $content = "{$task} for {$applicationMaker} ({$startDate} - {$endDate})";

                    $rangeData = array(
                        'highlight' => 'purple',
                        'dates' => [
                            [
                                new DateTime($application['start']),
                                new DateTime($application['end'])
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
                                new DateTime($app['start']),
                                new DateTime($app['end'])
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

                    $rangeData = array(
                        'highlight' => 'red',
                        'dates' => [
                            [
                                new DateTime($app['start']),
                                new DateTime($app['end'])
                            ]
                        ],
                        'popover' => [
                            'label' => "Rejected by {$manager['name']}: {$app['rejectReason']}",
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
                                new DateTime($app['start']),
                                new DateTime($app['end'])
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
                                new DateTime($app['start']),
                                new DateTime($app['end'])
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
                    // erroroneous -- ignore
                    break;
                }
            }
        }
        return $data;
    }
}
