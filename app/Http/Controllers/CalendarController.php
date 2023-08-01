<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Nomination;
use App\Models\Application;
use \DateTime;

class CalendarController extends Controller
{
    public function getCalendarData(Request $request, String $user_id) {
        // Check if user exists for given user id
        if (!User::find($user_id)) {
            // User does not exist, return exception
            return response()->json(['error' => 'User does not exist.'], 500);
        }

        $data = array();

        // Get all applications of user
        $applications = Application::where("accountNo", "=" , $user_id)->get();

        // Iterate through each application and add data to data array
        foreach ($applications as $app) {
            switch ($app['status']) {
                case 'Y': {
                    // Application is approved
                    $rangeData = array(
                        'highlight' => 'green',
                        'dates' => [
                            [
                                $app['start'],
                                $app['end']
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
                    $manager = User::find($app['processedBy']);

                    $rangeData = array(
                        'highlight' => 'red',
                        'dates' => [
                            [
                                $app['start'],
                                $app['end']
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
                                $app['start'],
                                $app['end']
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
                    $nominations = Nomination::where('applicationNo', $app['id'])->get();

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
                                $app['start'],
                                $app['end']
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

        return response()->json($data);
    }
}
