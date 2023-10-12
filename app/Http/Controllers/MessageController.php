<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\Account;
use App\Models\Application;
use App\Models\EmailPreference;
use App\Models\Nomination;
use App\Models\ManagerNomination;
use DateTime;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mailer\Exception\TransportException;
use App\Models\UnsentEmail;

use App\Http\Controllers\EmailController;
use App\Jobs\SendAppCanceledManager;
use App\Jobs\SendAppWaitingRev;
use App\Jobs\SendNominationCancelled;
use App\Jobs\SendNominationDeclined;
use App\Jobs\SendNominationEmail;
use App\Jobs\SendNominationsCancelled;
use App\Jobs\SendNomineeAppEdited;
use App\Jobs\SendSubPeriodEditSubset;
use App\Jobs\SendApplicationDecision;
use App\Jobs\SendConfirmSubstitutions;
use App\Jobs\SendSystemNotification;

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
            } else {
                // senderNo is null, therefore sender is the system
                $message["senderName"] = "SYSTEM";
            }


            // get nominations if subject is Substitution Request or Edited Substitution Request
            if ($message['subject'] == "Substitution Request"
                || $message['subject'] == "Edited Substitution Request") {
                // applicationNo SHOULD exist
                // Get all nominations for the application where the nomineeNo == accountNo

                $nominations = Nomination::where('applicationNo', $message['applicationNo'], 'and')
                    ->where('nomineeNo', $accountNo)->get();
                $managerNominations = ManagerNomination::where('applicationNo', $message['applicationNo'], 'and')
                    ->where('nomineeNo', $accountNo)->get();

                $count = count($nominations) + count($managerNominations);
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
    public function acknowledgeMessage(Request $request)
    {
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
    public function notifyManagerApplicationAwaitingReview(String $superiorNo, int $applicationNo)
    {
        $application = Application::where('applicationNo', $applicationNo)->first();

        // Generate content for message
        $content = [
            "Nomination/s:",
        ];

        // Get all nominations for application
        $nominations = Nomination::where('applicationNo', $applicationNo)->get();

        $isSelfNominatedAll = true;

        $arr1 = Nomination::where('applicationNo', $applicationNo)->get();
        $arr2 = ManagerNomination::where('applicationNo', $applicationNo)->get();
        $processed = [];
        $toProcess = [];
        foreach ($arr1 as $a) {
            array_push($toProcess, $a);
        }
        foreach ($arr2 as $a) {
            array_push($toProcess, $a);
        }

        // Process each nomination
        foreach ($toProcess as $nomination) {
            $nomineeNo = $nomination->nomineeNo;

            // Check if nomineeNo is not in processed nomineeNo's
            if (!in_array($nomineeNo, $processed)) {
                array_push($processed, $nomineeNo);

                $nominationsForNominee = Nomination::where('applicationNo', $applicationNo, "and")
                ->where('nomineeNo', $nomineeNo)->get();
                $managerNominationsForNominee = ManagerNomination::where('applicationNo', $applicationNo, "and")
                ->where('nomineeNo', $nomineeNo)->get();

                $nominee = Account::where('accountNo', $nomineeNo)->first();

                // add name to content
                $name = "";

                if ($nomineeNo == $application->accountNo) {
                    $name = "Self Nomination";
                }
                else {
                    $name = "{$nominee->fName} {$nominee->lName} - {$nomineeNo}@curtin.edu.au";
                    $isSelfNominatedAll = false;
                }

                array_push(
                    $content,
                    "• {$name}"
                );

                // Add nominated roles of all nominations for nominee to content
                foreach ($nominationsForNominee as $nom) {
                    $roleName = app(RoleController::class)->getRoleFromAccountRoleId($nom->accountRoleId);

                    array_push(
                        $content,
                        "→{$roleName}"
                    );
                }

                // Add nominated roles of all manager nominations for nominee to content
                foreach ($managerNominationsForNominee as $nom) {
                    $subordinate = Account::where('accountNo', $nom->subordinateNo)->first();
                    $roleName = "Line Manager for ({$nom['subordinateNo']}) {$subordinate['fName']} {$subordinate['lName']}";

                    array_push(
                        $content,
                        "→{$roleName}"
                    );
                }
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

        date_default_timezone_set("UTC");
        Message::create([
            'applicationNo' => $applicationNo,
            'receiverNo' => $superiorNo,
            'senderNo' => $application->accountNo,
            'subject' => 'Application Awaiting Review',
            'content' => json_encode($content),
            'acknowledged' => false,
        ]);

        $preferences = EmailPreference::where('accountNo', $superiorNo)->first();
        $hours = $preferences->hours;
        if( $hours == 0 ) // If user is on instant notifications
        {
            // collect require data, and queue an email
            $data = [$superiorNo, $application->accountNo, $content];
            SendAppWaitingRev::dispatch($data, false, -1);
        }
    }


    /*
    Notifies nominees that they have been nominated for a newly created application
    */
    public function notifyNomineesApplicationCreated($applicationNo)
    {
        $application = Application::where('applicationNo', $applicationNo)->first();

        // List of processed nomineeNo's
        $processed = [
        ];

        $arr1 = Nomination::where('applicationNo', $applicationNo)
        ->where('nomineeNo', '!=', $application->accountNo)->get();
        $arr2 = ManagerNomination::where('applicationNo', $applicationNo)
        ->where('nomineeNo', '!=', $application->accountNo)->get();
        $toProcess = [];
        foreach ($arr1 as $a) {
            array_push($toProcess, $a);
        }
        foreach ($arr2 as $a) {
            array_push($toProcess, $a);
        }

        // Process each nomination
        foreach ($toProcess as $nomination) {
            $nomineeNo = $nomination->nomineeNo;

            // Check if nomineeNo is not in processed nomineeNo's
            if (!in_array($nomineeNo, $processed)) {
                array_push($processed, $nomineeNo);
                $content = [];

                $nominationsForNominee = Nomination::where('applicationNo', $applicationNo, "and")
                    ->where('nomineeNo', $nomineeNo)->get();
                $managerNominationsForNominee = ManagerNomination::where('applicationNo', $applicationNo, "and")
                    ->where('nomineeNo', $nomineeNo)->get();


                $count = count($nominationsForNominee->toArray()) + count($managerNominationsForNominee->toArray());

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

                // Add nominated roles of all manager nominations for nominee to content
                foreach ($managerNominationsForNominee as $nom) {
                    $subordinate = Account::where('accountNo', $nom->subordinateNo)->first();
                    $roleName = "Line Manager for ({$nom['subordinateNo']}) {$subordinate['fName']} {$subordinate['lName']}";

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

                // $preferences = EmailPreference::where('accountNo', $nomineeNo)->first();
                // $hours = $preferences->hours;
                // if( $hours == 0 ) // If user is on instant notificaitons
                // {
                //     // Collect data and queue an email
                //     $data = [$nomineeNo, $content];
                //     SendNominationEmail::dispatch($data);
                // }
            }
        }
    }


    /*
    Notifies line manager of an application being cancelled
    */
    public function notifyManagerApplicationCancelled(String $superiorNo, int $applicationNo)
    {
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
            'subject' => "Application Cancelled",
            'content' => json_encode($content),
            'acknowledged' => false,
        ]);

        // $preferences = EmailPreference::where('accountNo', $superiorNo)->first();
        // $hours = $preferences->hours;
        // if( $hours == 0 ) // If on instant notifications
        // {
        //     // Collect data and queue an email
        //     $data = [$superiorNo, $content, $application->accountNo];
        //     SendAppCanceledManager::dispatch($data);
        // }
    }

    /*
    Notifies nominee of an application being cancelled
    */
    public function notifyNomineeApplicationCancelled(String $nomineeNo, int $applicationNo)
    {
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
            'subject' => "Nomination Cancelled",
            'content' => json_encode($content),
            'acknowledged' => false,
        ]);

        // $preferences = EmailPreference::where('accountNo', $nomineeNo)->first();
        // $hours = $preferences->hours;
        // if( $hours == 0 ) // If user on instant notificatoins
        // {
        //     // Collect data and queue an email
        //     $data = [$nomineeNo, $content, $application->accountNo];
        //     SendNominationCancelled::dispatch($data);
        // }
    }

    /*
    Notifies nominees of nomination cancellation
        e.g. a role they were nominated for was changed to a different nominee
    */
    public function notifyNomineeNominationCancelled(array $removedNominations, array $removedManagerNominations, String $applicationNo)
    {
        $application = Application::where('applicationNo', $applicationNo)->first();

        //combine into array of nomineeNos
        $nomineeNos = array();

        foreach ($removedNominations as $nomineeNo => $accountRoleIds) {
            array_push($nomineeNos, $nomineeNo);
        }
        foreach ($removedManagerNominations as $nomineeNo => $accountRoleIds) {
            array_push($nomineeNos, $nomineeNo);
        }

        // Iterate through each nomineeNo
        foreach ($nomineeNos as $nomineeNo) {
            $content = ["You have been un-nominated for the following roles:"];

            // process if nomineeNo is a key in removedNominations
            if (array_key_exists($nomineeNo, $removedNominations)) {
                // Iterate through accountRoleIds and get roleName and add to content list
                foreach ($removedNominations[$nomineeNo] as $accountRoleId) {
                    // Get role name
                    $roleName = app(RoleController::class)->getRoleFromAccountRoleId($accountRoleId);

                    array_push(
                        $content,
                        "→{$roleName}",
                    );
                }
            }
            // process if nomineeNo is a key in removedManagerNominations
            if (array_key_exists($nomineeNo, $removedManagerNominations)) {
                // Iterate through subordinateNos and add to content list
                foreach ($removedManagerNominations[$nomineeNo] as $subordinateNo) {
                    $sub = Account::where('accountNo', $subordinateNo)->first();
                    $role = "Line Manager for ({$sub->accountNo}) {$sub->fName} {$sub->lName}";

                    array_push(
                        $content,
                        "→{$role}",
                    );
                }
            }

            array_push($content, "You no longer need to takeover these roles if you have previously accepted them for this period of time.");
            array_push(
                $content,
                "Duration: {$application['sDate']} - {$application['eDate']}"
            );

            Message::create([
                'applicationNo' => $applicationNo,
                'receiverNo' => $nomineeNo,
                'senderNo' => $application->accountNo,
                'subject' => 'Nomination/s Cancelled',
                'content' => json_encode($content),
                'acknowledged' => false,
            ]);

            // $preferences = EmailPreference::where('accountNo', $nomineeNo)->first();
            // $hours = $preferences->hours;
            // if( $hours == 0 ) // if on instant notifications
            // {
            //     // Collect data and queue an email
            //     $data = [$nomineeNo, $content, ];
            //     SendNominationsCancelled::dispatch($data);
            // }
        }
    }



    /* Notifies nominees of application they agreed to substitute for being approved */
    public function notifyNomineesApplicationApproved($applicationNo) {
        $application = Application::where('applicationNo', $applicationNo)->first();

        // Process nominations
        $arr1 = Nomination::where('applicationNo', $applicationNo)
        ->where('nomineeNo', '!=', $application->accountNo)->get();
        $arr2 = ManagerNomination::where('applicationNo', $applicationNo)
        ->where('nomineeNo', '!=', $application->accountNo)->get();
        $toProcess = [];
        foreach ($arr1 as $a) {
            array_push($toProcess, $a);
        }
        foreach ($arr2 as $a) {
            array_push($toProcess, $a);
        }
        $processedNominees = [];

        foreach ($toProcess as $nomination) {
            if (!in_array($nomination->nomineeNo, $processedNominees)) {
                // add nomineeNo to array if not added
                array_push($processedNominees, $nomination->nomineeNo);

                $nominations = Nomination::where('applicationNo',  $applicationNo)
                    ->where('nomineeNo', $nomination->nomineeNo)
                    ->where('status', "Y")->get();

                $content = [
                    "An application you agreed to substitute for has been approved.",
                    "Roles you agreed to takeover:"
                ];

                foreach ($nominations as $nom) {
                    // Get role name
                    $roleName = app(RoleController::class)->getRoleFromAccountRoleId($nom->accountRoleId);

                    array_push(
                        $content,
                        "→{$roleName}",
                    );
                }

                $managerNominations = ManagerNomination::where('applicationNo', $applicationNo)
                ->where('nomineeNo', $nomination->nomineeNo)->where('status', "Y")->get();
                foreach ($managerNominations as $managerNomination) {
                    $sub = Account::where('accountNo', $managerNomination->subordinateNo)->first();
                    $role = "Line Manager for ({$sub->accountNo}) {$sub->fName} {$sub->lName}";

                    array_push(
                        $content,
                        "→{$role}",
                    );
                }

                array_push(
                    $content,
                    "Duration: {$application['sDate']} - {$application['eDate']}"
                );

                $message = Message::create([
                    'applicationNo' => $applicationNo,
                    'receiverNo' => $nomination->nomineeNo,
                    'senderNo' => $application->accountNo,
                    'subject' => 'Confirmed Substitutions',
                    'content' => json_encode($content),
                    'acknowledged' => false,
                ]);
            }
        }
    }

    /*
    Notifies nominees of edited applications that have only the period edited to become a subset
    */
    public function notifyNomineeApplicationEditedSubsetOnly($applicationNo, $nomineeNos)
    {
        $application = Application::where('applicationNo', $applicationNo)->first();

        // Process nominations
        $arr1 = Nomination::where('applicationNo', $applicationNo)
        ->where('nomineeNo', '!=', $application->accountNo)->get();
        $arr2 = ManagerNomination::where('applicationNo', $applicationNo)
        ->where('nomineeNo', '!=', $application->accountNo)->get();
        $toProcess = [];
        foreach ($arr1 as $a) {
            array_push($toProcess, $a);
        }
        foreach ($arr2 as $a) {
            array_push($toProcess, $a);
        }
        $processedNominees = [];

        foreach ($toProcess as $nomination) {
            if (!in_array($nomination->nomineeNo, $processedNominees)) {
                // add nomineeNo to array if not added
                array_push($processedNominees, $nomination->nomineeNo);

                $nominations = Nomination::where('applicationNo',  $applicationNo)
                    ->where('nomineeNo', $nomination->nomineeNo)->get();

                $content = [
                    "The applicant has edited their application's leave period to be a subset of the original leave period.",
                ];

                if ($application->status == "Y") {
                    array_push($content, "You will only need to take over the following roles for the updated shorter duration if you have accepted to become a substitute:");
                } else {
                    array_push($content, "Should the application get approved, you will only need to take over the following roles for the updated shorter duration if you have accepted to become a substitute:");
                }

                foreach ($nominations as $nom) {
                    // Get role name
                    $roleName = app(RoleController::class)->getRoleFromAccountRoleId($nom->accountRoleId);

                    array_push(
                        $content,
                        "→{$roleName}",
                    );
                }

                $managerNominations = ManagerNomination::where('applicationNo', $applicationNo)
                ->where('nomineeNo', $nomination->nomineeNo)->get();
                foreach ($managerNominations as $managerNomination) {
                    $sub = Account::where('accountNo', $managerNomination->subordinateNo)->first();
                    $role = "Line Manager for ({$sub->accountNo}) {$sub->fName} {$sub->lName}";

                    array_push(
                        $content,
                        "→{$role}",
                    );
                }

                array_push(
                    $content,
                    "Duration: {$application['sDate']} - {$application['eDate']}"
                );

                $message = Message::create([
                    'applicationNo' => $applicationNo,
                    'receiverNo' => $nomination->nomineeNo,
                    'senderNo' => $application->accountNo,
                    'subject' => 'Substitution Period Edited (Subset)',
                    'content' => json_encode($content),
                    'acknowledged' => false,
                ]);

                // $preferences = EmailPreference::where('accountNo', $nom->nomineeNo)->first();
                // $hours = $preferences->hours;
                // if( $hours == 0 ) // on instant notifications
                // {
                //     // Collect data and queue an email
                //     $data = [$nom->nomineeNo, $content, ];
                //     SendSubPeriodEditSubset::dispatch($data);
                // }
            }
        }
    }

    /*
    Notifies nominees of edited applications
    */
    public function notifyNomineeApplicationEdited($applicationNo, $nomineeNos)
    {
        $application = Application::where('applicationNo', $applicationNo)->first();

        foreach ($nomineeNos as $nomineeNo) {
            $content = [
                "This application has been edited.",
                "Please accept/reject based on the new details.",
                "You have been nominated for the following roles:",
            ];

            $nominations = Nomination::where('nomineeNo', $nomineeNo)
            ->where('applicationNo', $applicationNo)->get();
            $managerNominations = ManagerNomination::where('nomineeNo', $nomineeNo)
            ->where('applicationNo', $applicationNo)->get();

            // Iterate through nominations and get roleName based on accountRoleId and add to content list
            foreach ($nominations as $nomination) {
                // Get role name
                $accountRoleId = $nomination->accountRoleId;
                $roleName = app(RoleController::class)->getRoleFromAccountRoleId($accountRoleId);

                array_push(
                    $content,
                    "→{$roleName}",
                );
            }


            foreach ($managerNominations as $managerNomination) {
                $sub = Account::where('accountNo', $managerNomination->subordinateNo)->first();
                $role = "Line Manager for ({$sub->accountNo}) {$sub->fName} {$sub->lName}";

                array_push(
                    $content,
                    "→{$role}",
                );
            }


            array_push(
                $content,
                "Duration: {$application['sDate']} - {$application['eDate']}"
            );
            Message::create([
                'applicationNo' => $applicationNo,
                'receiverNo' => $nomineeNo,
                'senderNo' => $application->accountNo,
                'subject' => 'Edited Substitution Request',
                'content' => json_encode($content),
                'acknowledged' => false,
            ]);

            // $preferences = EmailPreference::where('accountNo', $nomineeNo)->first();
            // $hours = $preferences->hours;
            // if( $hours == 0 ) // on instant notifications
            // {
            //     // Collect data and queue an email
            //     $data = [$nomineeNo, $content, ];
            //     SendNomineeAppEdited::dispatch($data);
            // }
        }
    }

    /*
    Creates a message for the applicant of an application, indicating the
    decision on the application
    */
    public function notifyApplicantApplicationDecision($superiorNo, $applicationNo, $accepted, $rejectReason)
    {
        $application = Application::where("applicationNo", $applicationNo)->first();

        $subject = "Application Approved";
        $content = [];
        if (!$accepted) {
            $subject = "Application Denied";
            array_push(
                $content,
                "Your leave request was rejected:",
            );
            array_push(
                $content,
                "→{$rejectReason}"
            );
        } else {
            array_push(
                $content,
                "Your leave request was accepted.",
            );
        }

        array_push(
            $content,
            "Duration: {$application->sDate} - {$application->eDate}"
        );

        Message::create([
            'applicationNo' => $applicationNo,
            'receiverNo' => $application->accountNo,
            'senderNo' => $superiorNo,
            'subject' => $subject,
            'content' => json_encode($content),
            'acknowledged' => false,
        ]);

        // $preferences = EmailPreference::where('accountNo', $application->accountNo)->first();
        // $hours = $preferences->hours;
        // if( $hours == 0 ) // if on instant notifications
        // {
        //     // Collect data and queue an email
        //     $data = [$application->accountNo, $content, ];
        //     SendApplicationDecision::dispatch($data);
        // }
    }

    /*
    Creates a message for the applicant of an application, indicating that a nomination has been declined
    */
    public function notifyApplicantNominationDeclined($applicationNo, $nomineeNo, $rejectedRoles) {
        $application = Application::where("applicationNo", $applicationNo)->first();
        $nominee = Account::where('accountNo', $nomineeNo)->first();

        $subject = "Nomination/s Rejected";
        $content = [];

        if (count($rejectedRoles) > 1) {
            $content = [
                "{$nominee['fName']} {$nominee['lName']} has declined to takeover the following roles for Application#{$applicationNo}:",
            ];
        }
        else {
            $content = [
                "{$nominee['fName']} {$nominee['lName']} has declined to takeover the following role for Application#{$applicationNo}:",
            ];
        }

        foreach ($rejectedRoles as $role) {
            array_push(
                $content,
                "→{$role}"
            );
        }

        array_push(
            $content,
            "Duration: {$application->sDate} - {$application->eDate}",
            "The status of this application has been set to Denied by the system.",
            "Please edit the application with different nominees and resubmit for processing."
        );

        Message::create([
            'applicationNo' => $applicationNo,
            'receiverNo' => $application->accountNo,
            'senderNo' => $nomineeNo,
            'subject' => $subject,
            'content' => json_encode($content),
            'acknowledged' => false,
        ]);

        // $preferences = EmailPreference::where('accountNo', $application->accountNo)->first();
        // $hours = $preferences->hours;
        // if( $hours == 0 ) // on instant notifications
        // {
        //     // Collect data and queue an email
        //     $data = [$application->accountNo, $content, ];
        //     SendNominationDeclined::dispatch($data);
        // }
    }


    /*
    Creates a system wide notification
    ALL users will receive this message
    */
    public function createSystemNotification(Request $request) {
        $data = $request->all();
        $accountNo = $data['accountNo'];
        $content = $data['content'];

        // Check if user exists for given accountNo
        if (!Account::where('accountNo', $accountNo)->first()) {
            // User does not exist, return exception
            return response()->json(['error' => 'Account does not exist.'], 500);
        }
        // Verify that the account is a system admin account
        if (!Account::where('accountNo', $accountNo)->where('accountType', 'sysadmin')->first()) {
            // User is not a system admin, deny access to full table
            return response()->json(['error' => 'User not authorized for request.'], 500);
        }
        // Verify that the content length <= 300 and > 0
        if ($content == null || strlen($content) <= 0 || strlen($content) > 300) {
            return response()->json(['error' => 'Invalid content - length must be between 1 and 300 (inclusive).'], 500);
        }

        // get all accounts
        $accounts = Account::get();

        foreach ($accounts as $account) {
            Message::create([
                'applicationNo' => null,
                'receiverNo' => $account->accountNo,
                'senderNo' => $accountNo,
                'subject' => "System Notification",
                'content' => json_encode($content),
                'acknowledged' => false,
            ]);

            // $preferences = EmailPreference::where('accountNo', $account->accountNo)->first();
            // $hours = $preferences->hours;
            // if( $hours == 0 ) // if user is on instant notifications
            // {
            //     // Collect data and queue an email
            //     $data = [$account->accountNo, $content];
            //     SendSystemNotification::dispatch($data);
            // }
        }

        return response()->json(['success'], 200);
    }


    /*
   Returns all Messages
    */
   public function getAllMessages(Request $request, String $accountNo)
   {
       // Check if user exists for given accountNo
       if (!Account::where('accountNo', $accountNo)->first()) {
           // User does not exist, return exception
           return response()->json(['error' => 'Account does not exist.'], 500);
       }


       // Super admin can view all messages.
       if (Account::where('accountNo', $accountNo)->where('schoolId', 1)->exists()) {
           $messages = Message::get();
       }
       else {
           // Get schoolId of user
           $schoolCode = Account::select('schoolId')->where('accountNo', $accountNo)->first();
           //Log::info($schoolCode);

           $additionalApplications = Application::join('accounts', 'applications.accountNo', '=', 'accounts.accountNo')
                                               ->select('applications.applicationNo')
                                               ->where('schoolId', $schoolCode->schoolId)->get();


           Log::info($additionalApplications);


           $messages = Message::join('accounts', function($join) {
                                   $join->on('messages.receiverNo', '=', 'accounts.accountNo')
                                   ->orOn('messages.senderNo', '=', 'accounts.accountNo');
                               })
                               ->join('applications', 'messages.applicationNo', '=', 'applications.applicationNo')
                               ->select('messages.*')
                               ->distinct()
                               ->where('schoolId', $schoolCode->schoolId)
                               //->where('schoolId', 9) // For testing
                               ->orWhere(function ($query) use ($additionalApplications) {
                                   $query->whereIn('messages.applicationNo', $additionalApplications);
                               })->get();
       }

       return response()->json($messages);
   }

    // For each account, check if it's time to send an archive email,
    // and if so, send it.
    public function checkArchiveMessages()
    {
        $accounts = Account::get();
        foreach($accounts as $account) {
            // get email preferences
            $accountNo = $account->accountNo;
            $preferences = EmailPreference::get()->where('accountNo', $accountNo)->first();

            // calculate hours since last message sent
            $now = new DateTime('NOW');
            $lastSent = new DateTime($preferences->timeLastSent);
            $interval = $now->diff($lastSent);
            $hourInterval = ($interval->h) + ($interval->days * 24);

            // check if the time since an email was last sent is greater than the
            // correct frequency. Exclude users on instant notifications
            $frequency = $preferences->hours;
            if( $frequency != 0 && ($hourInterval > $frequency) )
            {
                // if so, send email
                $this->sendEmail($account, $preferences);
            }
        }
    }

    // check if an account has any messages, and send an archive email if so
    private function sendEmail($account, $preferences)
    {
        $messages = Message::where('receiverNo', $account->accountNo)->where('acknowledged', 0)->get();
        if ($messages->count() != 0) { // if Has messages

            $result = $account->sendDailyMessageNotification($messages);
            if( !$result )
            {
                // check if already has a backed up archive email
                if(!UnsentEmail::where('accountNo', $account->accountNo)
                ->where('subject', 'Unacknowledged Messages' )->first()){
                    UnsentEmail::create([ // create one if not
                        'accountNo' => $account->accountNo,
                        'subject' => 'Unacknowledged Messages',
                    ]);
                }
            }
            sleep(2); // to get around mailtrap emails per second limit
        }
    }

    // demo function for manually testing daily message functionality without emailing all accounts.
    public function demoSendDailyMessages()
    {
        $account1 = Account::where('accountNo', '000000a')->first();
        $messages1 = Message::where('receiverNo', '000000a')->where('acknowledged', 0)->get();
        $account2 = Account::where('accountNo', '000002L')->first();
        $messages2 = Message::where('receiverNo', '000002L')->where('acknowledged', 0)->get();
        $preferences = EmailPreference::get()->where('accountNo', $account2->accountNo)->first();

        if ($messages2->count() != 0) { // if Has messages
            try
            {   // send email
                $account2->sendDailyMessageNotification($messages2);
                $newTime = new DateTime('NOW');
                $preferences->timeLastSent = $newTime;
                $preferences->save();

            }
            catch( TransportException $e) // Email Sending Failed
            {
                // check if already has a backed up archive email
                if(!UnsentEmail::where('accountNo', $account2->accountNo)
                ->where('subject', 'Unacknowledged Messages' )->first()){
                    UnsentEmail::create([ // create one if not
                        'accountNo' => $account2->accountNo,
                        'subject' => 'Unacknowledged Messages',
                    ]);
                }
            }
        }
    }


    // function used only by MessageControllerTest to test notification functionality without
    // sending to all accounts. Do not call in any other context.
    public function sendDailyMessagesUnitTestFunction($user)
    {
        $messages = Message::where('receiverNo', $user->accountNo)->where('acknowledged', 0)->get();
        if ($messages->count() != 0) {
            $user->sendDailyMessageNotification($messages);
            sleep(2);
        }
    }
}
