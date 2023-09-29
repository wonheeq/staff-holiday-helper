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
    public function notifyNomineesApplicationCreated($applicationNo)
    {
        $application = Application::where('applicationNo', $applicationNo)->first();

        // List of processed nomineeNo's
        $processed = [
            $application->accountNo // Ignore application accountNo
        ];
        $nominations = Nomination::where('applicationNo', $applicationNo)->get();

        // Process each nomination
        foreach ($nominations as $nomination) {
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
    }

    /*
    Notifies nominees of nomination cancellation
        e.g. a role they were nominated for was changed to a different nominee
    */
    public function notifyNomineeNominationCancelled(array $removedNominations, String $applicationNo)
    {
        $application = Application::where('applicationNo', $applicationNo)->first();

        // Iterate through each key (nomineeNo), value (array of accountRoleIds)
        foreach ($removedNominations as $nomineeNo => $accountRoleIds) {
            $content = ["You have been un-nominated for the following roles:"];

            // Iterate through accountRoleIds and get roleName and add to content list
            foreach ($accountRoleIds as $accountRoleId) {
                // Get role name
                $roleName = app(RoleController::class)->getRoleFromAccountRoleId($accountRoleId);

                array_push(
                    $content,
                    "→{$roleName}",
                );
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
        }
    }

    /* Notifies nominees of application they agreed to substitute for being approved */
    public function notifyNomineesApplicationApproved($applicationNo) {
        $application = Application::where('applicationNo', $applicationNo)->first();

        // Process nominations
        $nominations = Nomination::where('applicationNo',  $applicationNo)
        ->where('nomineeNo', '!=', $application->accountNo)->get();
        $processedNominees = [];

        foreach ($nominations as $nomination) {
            if (!in_array($nomination->nomineeNo, $processedNominees)) {
                // add nomineeNo to array if not added
                array_push($processedNominees, $nomination->nomineeNo);

                $nominations = Nomination::where('applicationNo',  $applicationNo)
                    ->where('nomineeNo', $nomination->nomineeNo)->get();

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

                array_push(
                    $content,
                    "Duration: {$application['sDate']} - {$application['eDate']}"
                );

                $message = Message::create([
                    'applicationNo' => $applicationNo,
                    'receiverNo' => $nom->nomineeNo,
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
        $nominations = Nomination::where('applicationNo',  $applicationNo)
        ->where('nomineeNo', '!=', $application->accountNo)->get();
        $processedNominees = [];

        foreach ($nominations as $nomination) {
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

                array_push(
                    $content,
                    "Duration: {$application['sDate']} - {$application['eDate']}"
                );

                $message = Message::create([
                    'applicationNo' => $applicationNo,
                    'receiverNo' => $nom->nomineeNo,
                    'senderNo' => $application->accountNo,
                    'subject' => 'Substitution Period Edited (Subset)',
                    'content' => json_encode($content),
                    'acknowledged' => false,
                ]);
            }
        }
    }

    /*
    Notifies nominees of edited applications where they have been newly nominated
    */
    public function notifyNomineeApplicationEdited_NewNominee($applicationNo, $nomineeNos)
    {
        $application = Application::where('applicationNo', $applicationNo)->first();

        foreach ($nomineeNos as $nomineeNo) {
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
        } else {
            $messages = Message::get();
            return response()->json($messages);
        }
    }

    // get each account, call the sendMessage function for each.
    public function sendDailyMessages()
    {
        $accounts = Account::get();
        foreach ($accounts as $account) {
            $messages = Message::where('receiverNo', $account->accountNo)->where('acknowledged', 0)->get();
            if ($messages->count() != 0) {
                $account->sendDailyMessageNotification($messages);
                sleep(2); // to get around mailtrap emails per second limit
            }
        }
    }


    // demo function for manually testing daily message functionality without emailing all accounts.
    public function demoSendDailyMessages()
    {
        $account1 = Account::where('accountNo', '000000a')->first();
        $messages1 = Message::where('receiverNo', '000000a')->where('acknowledged', 0)->get();
        $account2 = Account::where('accountNo', '000002L')->first();
        $messages2 = Message::where('receiverNo', '000002L')->where('acknowledged', 0)->get();
        $account1->sendDailyMessageNotification($messages1);
        $account2->sendDailyMessageNotification($messages2);
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
