<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\Account;
use App\Models\Application;
use App\Models\Nomination;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    /*
    Returns all messages, formatted,  for the given account
    */
    public function getMessages(Request $request, String $accountNo)
    {
        // Check if Account exists for given accountNo
        if (!Account::where('accountNo', $accountNo)->first()) {
            // Account does not exist, return exception
            return response()->json(['error' => 'Account does not exist.'], 500);
        }

        $messages = Message::orderBy('created_at', 'asc')
            ->where('receiverNo', $accountNo)
            ->get();
        
        
        foreach ($messages as $message) {
            // Add in sendername of each message
            if ($message['senderNo'] != null) {
                // if sender is not null, then sender is a user
                $sender = app(UserController::class)->getUser($message["senderNo"]);
                $message["senderName"] = "{$sender['fName']} {$sender['lName']}";
            }
            else {
                // senderNo is null, therefore sender is the system
                $message["senderName"] = "SYSTEM";
            }


            // get nominations if subject is Substitution Request
            if ($message['subject'] == "Substitution Request") {
                // applicationNo SHOULD exist
                // Get all nominations for the application where the nomineeNo == accountNo

                $nominations = Nomination::where('applicationNo', $message['applicationNo'], 'and')
                                            ->where('nomineeNo', $accountNo)->get();

                $count = count($nominations);
                if ($count > 1) {
                    // add isNominatedMultiple flag to message data
                    $message["isNominatedMultiple"] = true;
                }
            }
        }

        return response()->json($messages);
    }


    /*
    Set the acknowledged status of a message to true
    */
    public function acknowledgeMessage(Request $request) {
        $data = $request->all();
        $accountNo = $data['accountNo'];
        $messageId = $data['messageId'];

        // Check if Account exists for given accountNo
        if (!Account::where('accountNo', $accountNo)->first()) {
            // Account does not exist, return exception
            return response()->json(['error' => 'Account does not exist.'], 500);
        }

        // Check if Message exists for given messageId
        $message = Message::where('messageId', $messageId)->first();
        if (!$message) {
            return response()->json(['error' => 'Message does not exist.'], 500);
        }

        // Check if Message belongs to user
        if ($message['receiverNo'] != $accountNo) {
            return response()->json(['error' => 'Message does not belong to user.'], 500);
        }

        $message->acknowledged = true;
        $message->save();

        return response()->json(['success'], 200);
    }


    /*
    Notifies line manager of a new application awaiting review
    */
    public function notifyManagerApplicationAwaitingReview(String $superiorNo, int $applicationNo) {
        $application = Application::where('applicationNo', $applicationNo)->first();

        // Generate content for message
        $content = [
            "Nomination/s:",
        ];

        // Get all nominations for application
        $nominations = Nomination::where('applicationNo', $applicationNo)->get();

        $isSelfNominatedAll = true;

        // Iterate through all nominations and add data to content
        // Set isSelfNominatedAll to false if not self nominated for all
        foreach ($nominations as $nom) {
            // Check if nomineeNo != applicant accountNo
            if ($nom->nomineeNo != $application->accountNo) {
                $isSelfNominatedAll = false;

                // Get nominee data
                $nominee = Account::where('accountNo', $nom->nomineeNo)->first();
                
                // Get role name
                $roleName = app(RoleController::class)->getRoleFromAccountRoleId($nom->accountRoleId);
                array_push(
                    $content,
                    "→{$nominee['fName']} {$nominee['lName']} - {$nom->nomineeNo}@curtin.wa.edu.au    {$roleName}"
                );
            }
        }

        // If isSelfNominatedAll is still true then add self nominated all message
        if ($isSelfNominatedAll) {
            array_push(
                $content,
                "→Applicant has noted that this period of leave will not affect their ability to handle their responsibilities"
            );
        }

        array_push(
            $content,
            "Duration: {$application['sDate']} - {$application['eDate']}"
        );
        Message::create([
            'applicationNo' => $applicationNo,
            'receiverNo' => $superiorNo,
            'senderNo' => $application->accountNo,
            'subject' => 'Application Awaiting Review',
            'content' => json_encode($content),
            'acknowledged' => false,
        ]);
    }

    /*
    Notifies nominees that they have been nominated for a newly created application
    */
    public function notifyNomineesApplicationCreated($applicationNo) {
        $application = Application::where('applicationNo', $applicationNo)->first();
        
        // List of processed nomineeNo's
        $processed = [
            $application->accountNo // Ignore application accountNo
        ];
        $nominations = Nomination::where('applicationNo', $applicationNo)->get();
                
        // Process each nomination
        foreach($nominations as $nomination) {
            $nomineeNo = $nomination->nomineeNo;

            // Check if nomineeNo is not in processed nomineeNo's
            if (!in_array($nomineeNo, $processed)) {
                array_push($processed, $nomineeNo);
                $content = [];

                $nominationsForNominee = Nomination::where('applicationNo', $applicationNo, "and")
                    ->where('nomineeNo', $nomineeNo)->get();
                $count = count($nominationsForNominee->toArray());
                
                array_push(
                    $content,
                    "You have been nominated for {$count} roles:"
                );

                // Add nominated roles of all nominations for nominee to content
                foreach ($nominationsForNominee as $nom) {
                    $roleName = app(RoleController::class)->getRoleFromAccountRoleId($nom->accountRoleId);

                    array_push(
                        $content,
                        "→{$roleName}"
                    );
                }

                array_push(
                    $content,
                    "Duration: {$application['sDate']} - {$application['eDate']}"
                );

                // Create message for nominee
                Message::create([
                    'applicationNo' => $applicationNo,
                    'receiverNo' => $nomineeNo,
                    'senderNo' => $application->accountNo,
                    'subject' => 'Substitution Request',
                    'content' => json_encode($content),
                    'acknowledged' => false,
                ]);
            }
        }
    }


    /*
    Notifies line manager of an application being cancelled
    */
    public function notifyManagerApplicationCancelled(String $superiorNo, int $applicationNo) {
        $application = Application::where('applicationNo', $applicationNo)->first();

        // Generate content for message
        $content = [
            "Application #{$applicationNo} was cancelled.",
            "Duration: {$application['sDate']} - {$application['eDate']}"
        ];

        Message::create([
            'applicationNo' => $applicationNo,
            'receiverNo' => $superiorNo,
            'senderNo' => $application->accountNo,
            'subject' => 'Application Cancelled',
            'content' => json_encode($content),
            'acknowledged' => false,
        ]);
    }

    /*
    Notifies nominee of an application being cancelled
    */
    public function notifyNomineeApplicationCancelled(String $nomineeNo, int $applicationNo) {
        $application = Application::where('applicationNo', $applicationNo)->first();

        // Generate content for message
        $content = [
            "An application you have been nominated for has been cancelled.",
            "Duration: {$application['sDate']} - {$application['eDate']}"
        ];

        Message::create([
            'applicationNo' => $applicationNo,
            'receiverNo' => $nomineeNo,
            'senderNo' => $application->accountNo,
            'subject' => 'Application Cancelled',
            'content' => json_encode($content),
            'acknowledged' => false,
        ]);
    }

    /*
    Notifies nominee of nomination cancellation
        e.g. a role they were nominated for was changed to a different nominee
    */
    public function notifyNomineeNominationCancelled(Nomination $nomination) {
        $application = Application::where('applicationNo', $nomination->applicationNo)->first();
        
    }

    /*
    Calls the necessary methods to handle notifying nominees of an application being edited
    */
    public function handleNotifyNomineeApplicationEdited($applicationNo, $oldDates, $oldNominations) {
        $application = Application::where('applicationNo', $applicationNo)->first();
        $newNominations = Nomination::where('applicationNo', $applicationNo)->get();
        
        $cancelledNominations = []; // No longer an nominee
        $updatedNominationsSubset = []; // Still an nominee for the same role, but period changed to be subset
        $updatedNominationsOutOfRange = []; // Still an nominee for the same role, but period changed to be out of old range

        // process notifications for old nominations
        foreach ($oldNominations as $old) {
            // compare to new nominations
            foreach ($newNominations as $new) {
                // accountRoleId and nomineeNo match
                if ($new->nomineeNo == $old->nomineeNo && $new->accountRoleId == $old->accountRoleId) {
                    // add to updatedNominationsSubset or updatedNominationsOutOfRange if period was changed
                    
                    // make sure that new period is not exactly same as old period
                    if ($application->sDate != $oldDates['start'] && $application->eDate != $oldDates['end']) {
                        // check if new period is in same or subset of old period
                        if ($application->sDate >= $oldDates['start'] && $application->eDate <=  $oldDates['end']) {
                            // create new array in updatedNominationsSubset using the nomineeNo if it doesn't exist
                            if ($updatedNominationsSubset[$old->nomineeNo] == null) {
                                $updatedNominationsSubset[$old->nomineeNo] = array();
                            }
 
                            // Get role name
                            $roleName = app(RoleController::class)->getRoleFromAccountRoleId($new->accountRoleId);

                            array_push(
                                $updatedNominationsSubset[$old->nomineeNo],
                                $roleName,
                            );
                        }
                        // Is not same or subset therefore is out of range
                        else {
                            // create new array in updatedNominationsOutOfRange using the nomineeNo if it doesn't exist
                            if ($updatedNominationsOutOfRange[$old->nomineeNo] == null) {
                                $updatedNominationsOutOfRange[$old->nomineeNo] = array();
                            }
                            
                            // Get role name
                            $roleName = app(RoleController::class)->getRoleFromAccountRoleId($new->accountRoleId);

                            array_push(
                                $updatedNominationsOutOfRange[$new->nomineeNo],
                                $roleName,
                            );
                        }
                    }
                    break;
                }
                // accountRoleIds match, but nominee has changed
                else if ($new->accountRoleId == $old->accountRoleId && $new->nomineeNo != $old->nomineeNo) {
                    // create new array in cancelledNominations using the old nomineeNo if it doesn't exist
                    if ($cancelledNominations[$old->nomineeNo] == null) {
                        $cancelledNominations[$old->nomineeNo] = array();
                    }

                    // Get role name
                    $roleName = app(RoleController::class)->getRoleFromAccountRoleId($new->accountRoleId);

                    // add cancelled role name to cancelledNominations[nomineeNo]
                    array_push(
                        $cancelledNominations[$old->nomineeNo],
                        $roleName
                    );
                    break;
                }
            }
        }
    }
}
 