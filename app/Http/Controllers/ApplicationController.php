<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\Nomination;
use App\Models\Account;
use App\Models\Message;
use App\Models\ManagerNomination;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\Log;

class ApplicationController extends Controller
{
    /*
    Returns all Applications belonging to the given account number.
    Each application also has the list of the nominations for the application
    */
    public function getApplications(Request $request, String $accountNo)
    {
        // Check if user exists for given user id
        if (!Account::where('accountNo', $accountNo)->first()) {
            // User does not exist, return exception
            return response()->json(['error' => 'Account does not exist.'], 500);
        }

        $applications = Application::orderBy('created_at', 'desc')->where('accountNo', $accountNo)->get();

        foreach ($applications as $val) {
            // get nominations for application and insert
            $nominations = app(NominationController::class)->getNominations($val["applicationNo"]);
            $nominationsToDisplay = app(NominationController::class)->getNominationsToDisplay($val["applicationNo"]);

            // check if is self nominated for all
            if ($this->isSelfNominatedAll($nominations, $accountNo)) {
                $val['isSelfNominatedAll'] = true;
            } else {
                $val["nominations"] = $nominations;
                $val["nominationsToDisplay"] = $nominationsToDisplay;
            }
            // get name of user who processed the application
            if ($val["processedBy"] != null) {
                $acc = Account::where('accountNo', $val['processedBy'])->first();
                $val["processedBy"] = "{$acc['fName']} {$acc['lName']}";
            }
            else {
                $val["processedBy"] = "System";
            }
        }

        return response()->json($applications);
    }



    /*
    Returns all applications
     */
    public function getAllApplications(Request $request, String $accountNo)
    {
        // Check if user exists for given accountNo
        if (!Account::where('accountNo', $accountNo)->first()) {
            // User does not exist, return exception
            return response()->json(['error' => 'Account does not exist.'], 500);
        } else {
            $applications = Application::get();
            return response()->json($applications);
        }
    }
    /*
    public function getAllApplications(Request $request)
    {
        $applications = Application::get();
        return response()->json($applications);
        //return Applications::all();
    }
*/
    /*
    Returns true if for all elements, nomineeNo == accountNo
    */
    private function isSelfNominatedAll($nominations, $accountNo)
    {
        foreach ($nominations as $nomination) {
            if ($nomination['nomineeNo'] != $accountNo) {
                return false;
            }
        }

        return true;
    }

    private function validateApplication($data)
    {
        // Date is empty
        if ($data['sDate'] == null || $data['eDate'] == null) {
            return array(
                'valid' => false,
                'reason' => 'A date is empty'
            );
        }

        $startDate = new DateTime($data['sDate']);
        $endDate = new DateTime($data['eDate']);
        $currentDate = new DateTime();
        $currentDate->setTimezone(new DateTimeZone("Australia/Perth"));

        // End date is earlier or equal to start date
        if ($endDate->getTimestamp() - $startDate->getTimestamp() <= 0) {
            return array(
                'valid' => false,
                'reason' => 'End date/time is earlier or equal to the start date/time'
            );
        }

        // A date is in the past
        if ($startDate->getTimestamp() - $currentDate->getTimestamp() <= 0
            || $endDate->getTimestamp() - $currentDate->getTimestamp() <= 0 ) {
            return array(
                'valid' => false,
                'reason' => 'A date is in the past'
            );
        }

        // nominations is empty
        if ($data['nominations'] == null || ($data['nominations'] && count($data['nominations']) == 0)) {
            return array(
                'valid' => false,
                'reason' => 'Missing nominations'
            );
        }

        if (!$data['selfNominateAll']) {
            $filteredForNull = array_filter($data['nominations'], function ($var) {
                if ($var['nomineeNo'] != null) {
                    return $var;
                }
            });
            // a nomineeNo is empty
            if (count($filteredForNull) != count($data['nominations'])) {
                return array(
                    'valid' => false,
                    'reason' => 'A nomineeNo is missing'
                );
            }

            $filteredForSelfNom = array_filter($data['nominations'], function ($var) use ($data) {
                if ($var['nomineeNo'] == $data['accountNo']) {
                    return $var;
                }
            });
            // all nominations are self nomination but did not select agreement
            $count1 = count($filteredForSelfNom);
            $count2 = count($data['nominations']);
            if (count($filteredForSelfNom) == count($data['nominations'])) {
                return array(
                    'valid' => false,
                    'reason' => 'All nominations are self nomination but, the agreement was not selected'
                );
            }
        }

        foreach ($data['nominations'] as $nomination) {
            // An AccountRoleId is null
            if ($nomination['accountRoleId'] == null) {
                return array(
                    'valid' => false,
                    'reason' => 'An accountRoleId is null'
                );
            }

            // Nomination for Line Manager for USER is the USER
            if ($nomination['accountRoleId'] == "MANAGER"
            && array_key_exists("subordinateNo", $nomination)
            && $nomination['nomineeNo'] == $nomination['subordinateNo']) {
                return array(
                    'valid' => false,
                    'reason' => "Cannot nominate user ({$nomination['nomineeNo']}) to become their own line manager"
                );
            }
        }

        return array(
            'valid' => true,
        );
    }

    /*
    Returns a date formatted to mysql timestamp
    */
    private function formatDate(string $date)
    {
        return str_replace('T', ' ', $date);
    }

    /*
    Creates a new Application in the database if the content is valid.
    Returns an Application encoded in json.
    */
    public function createApplication(Request $request)
    {
        $data = $request->all();

        $validation = $this->validateApplication($data);
        if (!$validation['valid']) {
            return response()->json($validation['reason'], 500);
        }

        $application = null;

        // If self nominated for all, application status should be Undecided
        if ($data['selfNominateAll']) {
            $application = Application::create([
                'accountNo' => $data['accountNo'],
                'sDate' => $this->formatDate($data['sDate']),
                'eDate' => $this->formatDate($data['eDate']),
                'status' => 'U',
            ]);
        } else {
            $application = Application::create([
                'accountNo' => $data['accountNo'],
                'sDate' => $this->formatDate($data['sDate']),
                'eDate' => $this->formatDate($data['eDate']),
                'status' => 'P',
            ]);
        }

        // Generate nominations for application
        foreach ($data['nominations'] as $nomination) {
            // if nomineeNo is Self Nomination, $nominee is applicant accountNo, else the provided nomineeNo
            $status = 'U';
            // If nomineeNo == applicant accountNo then set status of nomination to 'Y', otherwise keep as 'U'
            if ($nomination['nomineeNo'] == $data['accountNo']) {
                $status = 'Y';
            }

            // check it is a nomination for the line manager role
            if ($nomination['accountRoleId'] == 'MANAGER') {
                // Create ManagerNomination
                $sub = ManagerNomination::create([
                    'applicationNo' => $application->applicationNo,
                    'nomineeNo' => $nomination['nomineeNo'],
                    'subordinateNo' => $nomination['subordinateNo'],
                    'status' => $status
                ]);
            }
            else {
                $newNom = Nomination::create([
                    'applicationNo' => $application->applicationNo,
                    'nomineeNo' => $nomination['nomineeNo'],
                    'accountRoleId' => $nomination['accountRoleId'],
                    'status' => $status
                ]);
            }
        }


        // Inform line manager of new application to review (if self-nominated all)
        if ($application->status == 'U') {
            // Get current line manager account number
            $superiorNo = app(AccountController::class)->getCurrentLineManager($data['accountNo'])->accountNo;
            // Notify line manager of new application to review
            app(MessageController::class)->notifyManagerApplicationAwaitingReview($superiorNo, $application->applicationNo);
        }
        // Not all nominations were self-nominations, group together roles and inform all nominees
        app(MessageController::class)->notifyNomineesApplicationCreated($application->applicationNo);

        $accountNo = $data['accountNo'];
        $applicationNo = $application->applicationNo;
        $result = Application::where('applicationNo', $applicationNo)->first();
        // get nominations for application and insert
        $nominations = app(NominationController::class)->getNominations($applicationNo);
        $nominationsToDisplay = app(NominationController::class)->getNominationsToDisplay($applicationNo);

        // check if is self nominated for all
        if ($this->isSelfNominatedAll($nominations, $accountNo)) {
            $result['isSelfNominatedAll'] = true;
        } else {
            $result["nominations"] = $nominations;
            $result["nominationsToDisplay"] = $nominationsToDisplay;
        }
        return response()->json($result);
    }

    /*
    handles the logic for updating and saving the application
    */
    public function handleEditApplication(array $data)
    {
        $application = Application::where('applicationNo', $data['applicationNo'])->first();

        // If self nominated for all, application status should be Undecided
        if (!$data['selfNominateAll']) {
            $application->status = 'U';
        } else {
            $application->status = 'P';
        }

        // edit other attributes
        $application->sDate = $this->formatDate($data['sDate']);
        $application->eDate = $this->formatDate($data['eDate']);
        $application->processedBy = null;
        $application->rejectReason = null;
        $application->save();

        return $application;
    }

    /*
    Checks if each nominee from the old nominations has been removed as a nomination in any of the new nominations
    Handles deleting of old nominations from the database
    Handles sending grouped messages notifying nominees of cancelled nominations
    */
    public function handleEditApplicationCancelledNominations($oldNominations, $oldManagerNominations, $newNominations, $applicationNo)
    {
        $removedNominations = [];
        $removedManagerNominations = [];


        // Iterate through old nomination data
        foreach ($oldNominations as $old) {
            $foundInNew = false;
            // Iterate through new nominations
            foreach ($newNominations as $new) {
                if ($new['accountRoleId'] == "MANAGER") {
                    continue;
                }

                // Check if we can find the old data in the new
                if ($old->nomineeNo == $new['nomineeNo'] && $old->accountRoleId == $new['accountRoleId']) {
                    $foundInNew = true;
                    break;
                }
            }
            // If the old nomination data wasn't found in the new nomination data
            // AKA The nominee was removed as a nominee
            if (!$foundInNew) {
                // Delete old nomination from database
                Nomination::where('applicationNo', $applicationNo, "and")
                    ->where('nomineeNo', $old->nomineeNo, "and")
                    ->where('accountRoleId', $old->accountRoleId)->delete();

                // create new array with nomineeNo as key inside removedNominations if it doesn't exist
                // make sure not to add applicant to this array
                if ($old->nomineeNo != Application::where('applicationNo', $applicationNo)->first()->accountNo) {
                    if (!array_key_exists($old->nomineeNo, $removedNominations)) {
                        $removedNominations[$old->nomineeNo] = array();
                    }
    
                    // Add to list of accountRoleIds the nominee was removed as a nominee for
                    array_push($removedNominations[$old->nomineeNo], $old->accountRoleId);
                }
            }

            // delete message associated with old nomination/s
            $message = Message::where('applicationNo', $applicationNo, "and")
            ->where('receiverNo', $old->nomineeNo, "and")
            ->where('subject', "Substitution Request")->delete();

            // delete message associated with old nomination/s
            $message = Message::where('applicationNo', $applicationNo, "and")
            ->where('receiverNo', $old->nomineeNo, "and")
            ->where('subject', "Edited Substitution Request")->delete();

        }

        // Iterate through old manager nomination data
        foreach ($oldManagerNominations as $old) {
            $foundInNew = false;
            // Iterate through new nominations
            foreach ($newNominations as $new) {
                if ($new['accountRoleId'] != "MANAGER") {
                    continue;
                }

                // Check if we can find the old data in the new
                if ($old->nomineeNo == $new['nomineeNo'] && $old->subordinateNo == $new['subordinateNo']) {
                    $foundInNew = true;
                    break;
                }
            }
            // If the old nomination data wasn't found in the new nomination data
            // AKA The nominee was removed as a nominee
            if (!$foundInNew) {
                // Delete old manager nomination from database
                ManagerNomination::where('applicationNo', $applicationNo, "and")
                    ->where('nomineeNo', $old->nomineeNo, "and")
                    ->where('subordinateNo', $old->subordinateNo)->delete();

                // create new array with nomineeNo as key inside removedManagerNominations if it doesn't exist
                // make sure not to add applicant to this array
                if ($old->nomineeNo != Application::where('applicationNo', $applicationNo)->first()->accountNo) {
                    if (!array_key_exists($old->nomineeNo, $removedManagerNominations)) {
                        $removedManagerNominations[$old->nomineeNo] = array();
                    }
    
                    // Add to list of subordinateNos the nominee was removed as a manager for
                    array_push($removedManagerNominations[$old->nomineeNo], $old->subordinateNo);
                }
            }

            // delete message associated with old nomination/s
            $message = Message::where('applicationNo', $applicationNo, "and")
            ->where('receiverNo', $old->nomineeNo, "and")
            ->where('subject', "Substitution Request")->delete();

            // delete message associated with old nomination/s
            $message = Message::where('applicationNo', $applicationNo, "and")
            ->where('receiverNo', $old->nomineeNo, "and")
            ->where('subject', "Edited Substitution Request")->delete();

        }

        // call method to create new messages
        app(MessageController::class)->notifyNomineeNominationCancelled($removedNominations, $removedManagerNominations, $applicationNo);
    }

    // Handle nonedited, edited nominations and creation of new nominations
    /*
    $remainingOldNominations - Array of Nominations from the database after any Nominations were deleted
    $oldNominations - Array of Nominations
    $newNominations - JSON Array of Nominations {
        'nomineeNo' => x,
        'accountRoleId' => x,
    }
    */
    public function handleEditApplicationNonCancelledNominations($oldNominations, $oldManagerNominations, $newNominations, $applicationNo, $oldDates)
    {
        // Get Remaining Old (Non deleted) Nominations
        $remainingOldNominations = Nomination::where('applicationNo', $applicationNo)->get();
        $remainingOldManagerNominations = ManagerNomination::where('applicationNo', $applicationNo)->get();
        $application = Application::where('applicationNo', $applicationNo)->first();

        // Store nominee numbers of nominees that
        // should receive an 'edited subset' type message
        // rather than the other type
        $shouldSendToNomineeAs_EditedSubsetOnly = [];

        $isSubset = false;
        $isOutOfRange = false;

        $newStartDate = new DateTime($application->sDate);
        $newEndDate = new DateTime($application->eDate);
        $oldStartDate = new DateTime($oldDates['start']);
        $oldEndDate = new DateTime($oldDates['end']);
        //Log::debug("Dates: old, new");
        //Log::debug($oldStartDate->format('Y-m-d H:i:s'));
        //Log::debug($oldEndDate->format('Y-m-d H:i:s'));
        //Log::debug($newStartDate->format('Y-m-d H:i:s'));
        //Log::debug($newEndDate->format('Y-m-d H:i:s'));

        /*Log::debug((($newStartDate >= $oldStartDate && $newEndDate <= $oldEndDate)
            && !($newStartDate == $oldStartDate && $newEndDate == $oldEndDate))
                ?"Is a subset"
                :(($newStartDate == $oldStartDate && $newEndDate == $oldEndDate)
                    ?"Period unchanged."
                    :"Period out of original range."));
                    */

        // Check if the period has been altered and if so if it is a subset or out of range
        if (($newStartDate >= $oldStartDate && $newEndDate <= $oldEndDate)
        && !($newStartDate == $oldStartDate && $newEndDate == $oldEndDate)) {
            // The new date range is within but not equal to the old date range
            // Therefore it is a subset
            $isSubset = true;

            // init shouldSendToNomineeAs_EditedSubsetOnly array
            foreach ($newNominations as $new) {
                // assume that nominees in new nominations should receive as editedsubset
                // unless they were a part of the deleted nominations in
                // handleEditApplicationCancelledNominations
                
                // Check if nomineeNo can be found in $remainingOldNominations
                foreach ($remainingOldNominations as $old) {
                    if ($new['accountRoleId'] == "MANAGER") { continue; }
                    if ($new['nomineeNo'] == $old->nomineeNo && $new['nomineeNo'] != $application->accountNo) {
                        if (!in_array($new['nomineeNo'], $shouldSendToNomineeAs_EditedSubsetOnly)) {
                            array_push($shouldSendToNomineeAs_EditedSubsetOnly, $new['nomineeNo']);
                        }
                        break;
                    }
                }

                // Check if nomineeNo can be found in $remainingOldManagerNominations
                foreach ($remainingOldManagerNominations as $old) {
                    if ($new['accountRoleId'] != "MANAGER") { continue; }
                    if ($new['nomineeNo'] == $old->nomineeNo && $new['nomineeNo'] != $application->accountNo) {
                        if (!in_array($new['nomineeNo'], $shouldSendToNomineeAs_EditedSubsetOnly)) {
                            array_push($shouldSendToNomineeAs_EditedSubsetOnly, $new['nomineeNo']);
                        }
                        break;
                    }
                }
            }
        }
        else {
            if (!($newStartDate == $oldStartDate && $newEndDate == $oldEndDate)) {
                // The new date range is not exactly equal to the old date range
                // Therefore it is out of range
                $isOutOfRange = true;
            }
        }
        //Log::debug("isSubset = ".($isSubset?'true':'false'));
        //Log::debug("isOutOfRange = ".($isOutOfRange?'true':'false'));

        $nomineesToSendAs_SubstitutionRequest = [];
        $nomineesToSendAs_EditedSubstitutionRequest = [];
        
        // Iterate through new nomination data
        foreach ($newNominations as $new) {
            $newInOld = false;
            // Iterate through remaining old nominations
            foreach ($remainingOldNominations as $old) {
                if ($new['accountRoleId'] == "MANAGER") { continue; }
                // Check if we can find entry that matches the accountRoleId AND nomineeNo of new
                // If so, then we need to update the existing entry
                if ($old->accountRoleId == $new['accountRoleId'] && $old->nomineeNo == $new['nomineeNo']) {
                    $newInOld = true;
                    break;
                }
            }

            // Iterate through remaining old manager nominations
            foreach ($remainingOldManagerNominations as $old) {
                if ($new['accountRoleId'] != "MANAGER") { continue; }
                // Check if we can find entry that matches the subordinateNo AND nomineeNo of new
                // If so, then we need to update the existing entry
                if ($old->subordinateNo == $new['subordinateNo'] && $old->nomineeNo == $new['nomineeNo']) {
                    $newInOld = true;
                    break;
                }
            }

            if ($newInOld) {
                // accountRoleId found in both new nomination data AND old remaining nominations
                // see if we need to update the status

                // Application period has been edited out of range, will need to confirm/reject via EditedSubstitionRequest
                if ($isOutOfRange) {
                    // Find existing nomination and edit status
                    if ($new['accountRoleId'] != "MANAGER") {
                        Nomination::where('applicationNo', $applicationNo)
                        ->where('accountRoleId', $new['accountRoleId'])->update([
                            'nomineeNo' => $new['nomineeNo'],
                            // status = 'Y' if self nominated, otherwise = 'U'
                            'status' => $new['nomineeNo'] == $application->accountNo ? 'Y' : 'U',
                        ]);
                    }
                    else {
                        ManagerNomination::where('applicationNo', $applicationNo)
                        ->where('subordinateNo', $new['subordinateNo'])->update([
                            'nomineeNo' => $new['nomineeNo'],
                            // status = 'Y' if self nominated, otherwise = 'U'
                            'status' => $new['nomineeNo'] == $application->accountNo ? 'Y' : 'U',
                        ]);
                    }
                     

                    // Ensure applicant is not added to this array
                    if ($new['nomineeNo'] != $application->accountNo) {
                        // Group under the edited substiton request array
                        if (!in_array($new['nomineeNo'], $nomineesToSendAs_EditedSubstitutionRequest)) {
                            //Log::debug("A");
                            array_push($nomineesToSendAs_EditedSubstitutionRequest, $new['nomineeNo']);
                        }
                    }
                }
                else if ($isSubset) {
                    if ($new['accountRoleId'] == "MANAGER") {
                        foreach ($remainingOldNominations as $rem) {
                            if ($rem['nomineeNo'] == $new['nomineeNo'] && $rem['accountRoleId'] == $new['accountRoleId']) {
                                if ($rem->status == 'U' || $rem->status == 'N') {
                                    if (!in_array($new['nomineeNo'], $nomineesToSendAs_EditedSubstitutionRequest)) {
                                        //Log::debug("D");
                                        Nomination::where('applicationNo', $applicationNo)
                                        ->where('accountRoleId', $new['accountRoleId'])->update([
                                            'nomineeNo' => $new['nomineeNo'],
                                            // status = 'Y' if self nominated, otherwise = 'U'
                                            'status' => $new['nomineeNo'] == $application->accountNo ? 'Y' : 'U',
                                        ]);
    
                                        //Log::debug("B");
                                        array_push($nomineesToSendAs_EditedSubstitutionRequest, $new['nomineeNo']);
                                    }
                                }
                                break;
                            }
                        }
                    }
                    else {
                        foreach ($remainingOldManagerNominations as $rem) {
                            if ($rem['nomineeNo'] == $new['nomineeNo'] && $rem['subordinateNo'] == $new['subordinateNo']) {
                                if ($rem->status == 'U' || $rem->status == 'N') {
                                    if (!in_array($new['nomineeNo'], $nomineesToSendAs_EditedSubstitutionRequest)) {
                                        Log::debug("D");
                                        ManagerNomination::where('applicationNo', $applicationNo)
                                        ->where('subordinateNo', $new['subordinateNo'])->update([
                                            'nomineeNo' => $new['nomineeNo'],
                                            // status = 'Y' if self nominated, otherwise = 'U'
                                            'status' => $new['nomineeNo'] == $application->accountNo ? 'Y' : 'U',
                                        ]);
    
                                        //Log::debug("B");
                                        array_push($nomineesToSendAs_EditedSubstitutionRequest, $new['nomineeNo']);
                                    }
                                }
                                break;
                            }
                        }
                    }
                }
                else {
                    // leftovers

                    //  Log::debug("Leftovers");
                    // Group under the eidted substiton request array IF all remaining old nominations not responded to 
                    if ($new['accountRoleId'] != "MANAGER") {
                        foreach ($remainingOldNominations as $rem) {
                            if ($rem['nomineeNo'] == $new['nomineeNo'] && $rem['accountRoleId'] == $new['accountRoleId']) {
                                if ($rem->status == 'U' || $rem->status == 'N') {
                                    if (!in_array($new['nomineeNo'], $nomineesToSendAs_EditedSubstitutionRequest)) {
                                        //Log::debug("C");
                                        Nomination::where('applicationNo', $applicationNo)
                                        ->where('accountRoleId', $new['accountRoleId'])->update([
                                            'nomineeNo' => $new['nomineeNo'],
                                            // status = 'Y' if self nominated, otherwise = 'U'
                                            'status' => $new['nomineeNo'] == $application->accountNo ? 'Y' : 'U',
                                        ]);
    
                                        array_push($nomineesToSendAs_EditedSubstitutionRequest, $new['nomineeNo']);
                                    }
                                }
                                break;
                            }
                        }
                    }
                    else {
                        foreach ($remainingOldManagerNominations as $rem) {
                            if ($rem['nomineeNo'] == $new['nomineeNo'] && $rem['subordinateNo'] == $new['subordinateNo']) {
                                if ($rem->status == 'U' || $rem->status == 'N') {
                                    if (!in_array($new['nomineeNo'], $nomineesToSendAs_EditedSubstitutionRequest)) {
                                        //Log::debug("C");
                                        ManagerNomination::where('applicationNo', $applicationNo)
                                        ->where('subordinateNo', $new['subordinateNo'])->update([
                                            'nomineeNo' => $new['nomineeNo'],
                                            // status = 'Y' if self nominated, otherwise = 'U'
                                            'status' => $new['nomineeNo'] == $application->accountNo ? 'Y' : 'U',
                                        ]);
    
                                        array_push($nomineesToSendAs_EditedSubstitutionRequest, $new['nomineeNo']);
                                    }
                                }
                                break;
                            }
                        }
                    }
                }
            }
            else {
                // new NOT found in old remaining data, therefore nominated for an
                // accountRoleId that previously was not assigned to a user or
                // the old nomination was deleted

                // Check if the nominee was previously nominated for the application
                $wasPreviouslyNominated = false;
                foreach($oldNominations as $old) {
                    if ($old->nomineeNo == $new['nomineeNo']) {
                        $wasPreviouslyNominated = true;
                        break;
                    }
                }

                foreach($oldManagerNominations as $old) {
                    if ($old->nomineeNo == $new['nomineeNo']) {
                        $wasPreviouslyNominated = true;
                        break;
                    }
                }

                if ($new['accountRoleId'] != "MANAGER") {
                     // Create new nomination
                    Nomination::create([
                        'applicationNo' => $applicationNo,
                        'nomineeNo' => $new['nomineeNo'],
                        'accountRoleId' => $new['accountRoleId'],
                        // status = 'Y' if self nominated, otherwise = 'U'
                        'status' => $new['nomineeNo'] == $application->accountNo ? 'Y' : 'U',
                    ]);
                }
                else {
                     // Create new Manager nomination
                    ManagerNomination::create([
                        'applicationNo' => $applicationNo,
                        'nomineeNo' => $new['nomineeNo'],
                        'subordinateNo' => $new['subordinateNo'],
                        // status = 'Y' if self nominated, otherwise = 'U'
                        'status' => $new['nomineeNo'] == $application->accountNo ? 'Y' : 'U',
                    ]);
                }
               

                // Ensure applicant is not added to these arrays
                if ($new['nomineeNo'] != $application->accountNo) {
                    // Was previously nominated so send message of type "Edited Substitution Request"
                    if ($wasPreviouslyNominated)
                    {
                        // Group under the edited substiton request array
                        if (!in_array($new['nomineeNo'], $nomineesToSendAs_EditedSubstitutionRequest)) {
                            array_push($nomineesToSendAs_EditedSubstitutionRequest, $new['nomineeNo']);
                        }
                    }
                    // Was not previously nominated so send message of type "Substition Request"
                    else {
                        // Group under the edited substiton request array
                        if (!in_array($new['nomineeNo'], $nomineesToSendAs_SubstitutionRequest)) {
                            array_push($nomineesToSendAs_SubstitutionRequest, $new['nomineeNo']);
                        }
                    }
                }
            }
        }

        // Remove any nomineeNos that appear in EditedSubsetOnly that appear in any of the other arrays
        foreach ($nomineesToSendAs_EditedSubstitutionRequest as $nomineeNo) {
            //Log::debug(array_search($nomineeNo, $shouldSendToNomineeAs_EditedSubsetOnly));
            if (array_search($nomineeNo, $shouldSendToNomineeAs_EditedSubsetOnly)) {
                //Log::debug("Removing {$nomineeNo}");
                $arrayIndex = array_search($nomineeNo, $shouldSendToNomineeAs_EditedSubsetOnly);
                array_splice($shouldSendToNomineeAs_EditedSubsetOnly, $arrayIndex);
            }
        }
        foreach ($nomineesToSendAs_SubstitutionRequest as $nomineeNo) {
            //Log::debug(array_search($nomineeNo, $shouldSendToNomineeAs_EditedSubsetOnly));

            if (array_search($nomineeNo, $shouldSendToNomineeAs_EditedSubsetOnly)) {
                //Log::debug("Removing {$nomineeNo}");
                $arrayIndex = array_search($nomineeNo, $shouldSendToNomineeAs_EditedSubsetOnly);
                array_splice($shouldSendToNomineeAs_EditedSubsetOnly, $arrayIndex);
            }
        }
        //Log::debug("Final Subset Array:");
        //Log::debug($shouldSendToNomineeAs_EditedSubsetOnly);

        // Remove any nomineeNos that appear in EditedSubstitionRequest and SubstitutionRequest from SubstitutionRequest
        foreach ($nomineesToSendAs_EditedSubstitutionRequest as $ESR) {
            foreach ($nomineesToSendAs_SubstitutionRequest as $SR) {
                if ($ESR == $SR) {
                    //Remove SR from it's array
                    $arrayIndex = array_search($SR, $nomineesToSendAs_SubstitutionRequest);
                    array_splice($nomineesToSendAs_SubstitutionRequest, $arrayIndex);
                    break;
                }
            }
        }

        $application = Application::where('applicationNo', $applicationNo)->first();
        $application->status = "U";
        $application->processedBy = null;
        $application->rejectReason = null;
        $noms = Nomination::where('applicationNo', $applicationNo)->get();
        foreach ($noms as $n) {
            if ($n->status != 'Y') {
                $application->status = "P";
                break;
            }
        }
        $mNoms = ManagerNomination::where('applicationNo', $applicationNo)->get();
        foreach ($mNoms as $n) {
            if ($n->status != 'Y') {
                $application->status = "P";
                break;
            }
        }
        $application->save();

        if (count($nomineesToSendAs_EditedSubstitutionRequest) > 0) {
            //Log::debug("SENT Edited Substition Request messages");
            //Log::debug($nomineesToSendAs_EditedSubstitutionRequest);
            app(MessageController::class)->notifyNomineeApplicationEdited($applicationNo, $nomineesToSendAs_EditedSubstitutionRequest);
        }
        if (count($shouldSendToNomineeAs_EditedSubsetOnly) > 0) {
            //Log::debug("SENT Application Period Edited (Subset) messages");
            app(MessageController::class)->notifyNomineeApplicationEditedSubsetOnly($applicationNo, $shouldSendToNomineeAs_EditedSubsetOnly);
        }
        if (count($nomineesToSendAs_SubstitutionRequest) > 0) {
            //Log::debug("SENT Substition Request messages");
            app(MessageController::class)->notifyNomineeApplicationEdited_NewNominee($applicationNo, $nomineesToSendAs_SubstitutionRequest);
        }
    }

    /*
    Edits an Application in the database if the content is valid.
    Returns an Application encoded in json.
    */
    public function editApplication(Request $request)
    {
        $data = $request->all();
        $accountNo = $data['accountNo'];
        $applicationNo = $data['applicationNo'];
        $application = Application::where('applicationNo', $applicationNo)->first();
        // Check if user exists for given user id
        if (!Account::where('accountNo', $accountNo)->first()) {
            // User does not exist, return exception
            return response()->json('Account does not exist.', 500);
        }

        $validation = $this->validateApplication($data);
        if (!$validation['valid']) {
            return response()->json($validation['reason'], 500);
        }

        // Get Old Nominations
        $oldNominations = Nomination::where('applicationNo', $data['applicationNo'])->get();
        // Get Old ManagerNominations
        $oldManagerNominations = ManagerNomination::where('applicationNo', $data['applicationNo'])->get();
        // store old dates
        $oldDates = [
            'start' => $application->sDate,
            'end' => $application->eDate,
        ];

        // EDIT APPLICATION
        $application = $this->handleEditApplication($data);

        // Delete old nominations where nomineeNo and accountRoleId not found in new nominations
        $this->handleEditApplicationCancelledNominations($oldNominations, $oldManagerNominations, $data['nominations'], $applicationNo);

        // Handle nonedited, edited nominations and creation of new nominations
        $this->handleEditApplicationNonCancelledNominations($oldNominations, $oldManagerNominations, $data['nominations'], $applicationNo, $oldDates);

        // Check if any nominations are of status U or N
        // If so, delete Application Awaiting Review message
        $noms = Nomination::where('applicationNo', $applicationNo)->get();
        foreach ($noms as $n) {
            if ($n->status != 'Y') {
                // A nomination's status is not 'Y' therefore the application should not get reviewed yet
                Message::where('applicationNo', $applicationNo)
                ->where('senderNo', $accountNo)
                ->where('subject', "Application Awaiting Review")
                ->delete();

                // Delete Confirmed Substitutions messages
                Message::where('applicationNo', $applicationNo)
                ->where('senderNo', $accountNo)
                ->where('subject', "Confirmed Substitutions")
                ->delete();
                break;
            }
        }
        $noms = ManagerNomination::where('applicationNo', $applicationNo)->get();
        foreach ($noms as $n) {
            if ($n->status != 'Y') {
                // A manager nomination's status is not 'Y' therefore the application should not get reviewed yet
                Message::where('applicationNo', $applicationNo)
                ->where('senderNo', $accountNo)
                ->where('subject', "Application Awaiting Review")
                ->delete();

                // Delete Confirmed Substitutions messages
                Message::where('applicationNo', $applicationNo)
                ->where('senderNo', $accountNo)
                ->where('subject', "Confirmed Substitutions")
                ->delete();
                break;
            }
        }


        $application = Application::where('applicationNo', $applicationNo)->first();

        // Inform line manager of new application to review (if self-nominated all)
        if ($application->status == 'U') {
            // Get current line manager account number
            $superiorNo = app(AccountController::class)->getCurrentLineManager($accountNo)->accountNo;
            // Notify line manager of new application to review
            Message::where('applicationNo', $applicationNo)
            ->where('senderNo', $accountNo)
            ->where('subject', "Application Awaiting Review")
            ->delete();
            // Delete Confirmed Substitutions messages
            Message::where('applicationNo', $applicationNo)
            ->where('senderNo', $accountNo)
            ->where('subject', "Confirmed Substitutions")
            ->delete();
            app(MessageController::class)->notifyManagerApplicationAwaitingReview($superiorNo, $application->applicationNo);
        }

        $result = Application::where('applicationNo', $applicationNo)->first();
        // get nominations for application and insert
        $nominations = app(NominationController::class)->getNominations($applicationNo);
        $nominationsToDisplay = app(NominationController::class)->getNominationsToDisplay($applicationNo);

        // check if is self nominated for all
        if ($this->isSelfNominatedAll($nominations, $accountNo)) {
            $result['isSelfNominatedAll'] = true;
        } else {
            $result["nominations"] = $nominations;
            $result["nominationsToDisplay"] = $nominationsToDisplay;
        }
        return response()->json($result);
    }


    /*
    Cancels an application by setting it's status to Cancelled
    */
    public function cancelApplication(Request $request, String $accountNo, String $appNo)
    {
        $applicationNo = intval($appNo);
        // Check if user exists for given user id
        if (!Account::where('accountNo', $accountNo)->first()) {
            // User does not exist, return exception
            return response()->json(['error' => 'Account does not exist.'], 500);
        }

        $application = Application::where('applicationNo', $applicationNo, "and")
            ->where('accountNo', $accountNo)->first();

        // Check if application exists and belongs to the user
        if (!$application) {
            // Application does not exist or does not belong to accountNo, return exception
            return response()->json(['error' => 'Application does not exist or does not belong to account.'], 500);
        }

        // Get current line manager account number
        $superiorNo = app(AccountController::class)->getCurrentLineManager($accountNo)->accountNo;
        // Notify manager of application cancellation if status was undecided or approved
        if ($application->status == 'Y' || $application->status == "U") {
            // Delete old message
            Message::where('receiverNo', $superiorNo, 'and')
                ->where('senderNo', $accountNo, 'and')
                ->where('applicationNo', $applicationNo, 'and')
                ->where('subject', "Application Awaiting Review")->delete();

            app(MessageController::class)->notifyManagerApplicationCancelled($superiorNo, $applicationNo);
        }

        // Set application status to Cancelled
        $application->status = 'C';
        $application->save();

        $processedNominees = [$accountNo];
        // Send cancelled application message for nominees
        $nominations = Nomination::where('applicationNo', $applicationNo)->get();
        foreach ($nominations as $nomination) {
            $nomineeNo = $nomination['nomineeNo'];

            // Check if nomineeNo is not in processedNominees
            if (!in_array($nomineeNo, $processedNominees)) {
                array_push($processedNominees, $nomineeNo);

                app(MessageController::class)->notifyNomineeApplicationCancelled($nomineeNo, $applicationNo);
            }
        }

        $managerNominations = ManagerNomination::where('applicationNo', $applicationNo)->get();
        foreach ($managerNominations as $nomination) {
            $nomineeNo = $nomination['nomineeNo'];

            // Check if nomineeNo is not in processedNominees
            if (!in_array($nomineeNo, $processedNominees)) {
                array_push($processedNominees, $nomineeNo);

                app(MessageController::class)->notifyNomineeApplicationCancelled($nomineeNo, $applicationNo);
            }
        }

        // Delete each 'Substitution Request' Message for the application
        Message::where('applicationNo', $applicationNo, "and")
            ->where('subject', "Substitution Request")->delete();

        // Delete each 'Edited Substitution Request' Message for the application
        Message::where('applicationNo', $applicationNo, "and")
        ->where('subject', "Edited Substitution Request")->delete();

        // Delete Confirmed Substitutions messages
        Message::where('applicationNo', $applicationNo)
        ->where('subject', "Confirmed Substitutions")
        ->delete();

        // Delete each nomination associated with the application
        //Nomination::where('applicationNo', $applicationNo)->delete();
        return response()->json(['success'], 200);
    }



    /*
    Gets all the details for the specified application
        Period
        Account Roles and Nominees
        Applicant Name
    Route: getApplicationForReview/{accountNo}/{applicationNo}
    */
    public function getApplicationForReview($accountNo, $applicationNo)
    {
        $applicationNo = intval($applicationNo);

        // Check if user exists for given user id
        $account = Account::where('accountNo', $accountNo)->first();
        if (!$account) {
            // User does not exist, return exception
            return response()->json(['error' => 'Account does not exist.'], 500);
        }

        $application = Application::where('applicationNo', $applicationNo, "and")->first();

        // Check that the application exists for the given applicationNo
        if (!$application) {
            return response()->json(['error' => 'Application does not exist.'], 500);
        }

        // Check that the application status is Undecided
        if ($application->status != 'U') {
            return response()->json(['error' => 'Application cannot be reviewed - nominee responses outstanding.'], 500);
        }

        // Check if applicant exists for given application
        $applicant = Account::where('accountNo', $application->accountNo)->first();
        if (!$applicant) {
            // User does not exist, return exception
            return response()->json(['error' => 'Applicant does not exist.'], 500);
        }

        // Check if the user is the superior or substitute superior of the account
        $substituteManager = app(AccountController::class)->getCurrentLineManager($applicant->accountNo);
        if ($accountNo != $substituteManager->accountNo && $accountNo != $applicant->superiorNo) {
            return response()->json(['error' => 'Invalid permissions to review application'], 500);
        }

        $nominationsRaw = [];
        $nominations = Nomination::where('applicationNo', $applicationNo)->get();
        // Iterate through nominations and format
        foreach ($nominations as $nom) {
            // Group by nomineeNo
            // If the nominee's accountNo does not exist as a key, create the default data
            if (!array_key_exists($nom->nomineeNo, $nominationsRaw)) {
                $nominee = Account::where('accountNo', $nom->nomineeNo)->first();
                $nominationsRaw[$nom->nomineeNo] = [
                    "nomineeNo" => $nom->nomineeNo,
                    "nomineeName" => "{$nominee->fName} {$nominee->lName} ({$nominee->accountNo})",
                    "roles" => []
                ];
            }

            // Add to roles
            array_push(
                $nominationsRaw[$nom->nomineeNo]["roles"],
                app(RoleController::class)->getRoleFromAccountRoleId($nom->accountRoleId)
            );
        }

        $managerNominations = ManagerNomination::where('applicationNo', $applicationNo)->get();
        // Iterate through manager nominations and format
        foreach ($managerNominations as $nom) {
            // Group by nomineeNo
            // If the nominee's accountNo does not exist as a key, create the default data
            if (!array_key_exists($nom->nomineeNo, $nominationsRaw)) {
                $nominee = Account::where('accountNo', $nom->nomineeNo)->first();
                $nominationsRaw[$nom->nomineeNo] = [
                    "nomineeNo" => $nom->nomineeNo,
                    "nomineeName" => "{$nominee->fName} {$nominee->lName} ({$nominee->accountNo})",
                    "roles" => []
                ];
            }

            $sub = Account::where('accountNo', $nom->subordinateNo)->first();

            // Add to roles
            array_push(
                $nominationsRaw[$nom->nomineeNo]["roles"],
                "Line Manager for ({$sub->accountNo}) {$sub->fName} {$sub->lName}"
            );
        }

        $nominationsFormatted = [];
        foreach ($nominationsRaw as $n) {
            array_push($nominationsFormatted, $n);
        }

        $data = [
            "applicationNo" => $applicationNo,
            "applicantNo" => $applicant->accountNo,
            "applicantName" => "{$applicant->fName} {$applicant->lName} ({$applicant->accountNo})",
            "duration" => "{$application->sDate} - {$application->eDate}",
            "nominations" => $nominationsFormatted
        ];
        return response()->json($data);
    }


    /*
    Sets the application's status to approved if valid
    */
    public function acceptApplication(Request $request)
    {
        $data = $request->all();
        $superiorNo = $data['accountNo'];
        $applicationNo = $data['applicationNo'];
        // Check if user exists for given user id
        if (!Account::where('accountNo', $superiorNo)->first()) {
            // User does not exist, return exception
            return response()->json(['error' => 'Account does not exist.'], 500);
        }

        // Check if application exists for the given applicationNo
        $application = Application::where('applicationNo', $applicationNo)->first();
        if (!$application) {

            return response()->json(['error' => 'Application does not exist.'], 500);
        }

        // Check that the application status is Undecided
        if ($application->status != 'U') {

            return response()->json(['error' => 'Application cannot be reviewed - nominee responses outstanding.'], 500);
        }

        // Check if applicant exists
        $applicant = Account::where('accountNo', $application->accountNo)->first();
        if (!$applicant) {

            return response()->json(['error' => 'Applicant does not exist.'], 500);
        }

        // Check if the applicant's superior or expected substitute is the given superiorNo
        $expectedSubstitute = app(AccountController::class)->getCurrentLineManager($applicant->accountNo);
        if ($superiorNo != $applicant->superiorNo && $superiorNo != $expectedSubstitute->accountNo) {
            return response()->json(['error' => 'Account is not the superior of applicant.'], 500);
        }

        // // Confirm that all nominees agreed
        // $nominations = Nomination::where("applicationNo", $applicationNo)->get();
        // foreach ($nominations as $n) {
        //     if ($n->status != "Y") {
        //         return response()->json(['error' => 'A nomination was not accepted.'], 500);
        //     }
        // }

        // set application status to 'Y'
        // set processedBy indicator to superiorNo
        $application->status = 'Y';
        $application->processedBy = $superiorNo;
        $application->save();

        // Mark Application Awaiting Review message as acknowledged
        $message = Message::where('applicationNo', $applicationNo, "and")
        ->where('senderNo', $applicant->accountNo, "and")
        ->where('subject', "Application Awaiting Review")->first();

        if ($message != null) {
            $message->acknowledged = true;
            $message->save();
        }

        // Message applicant that their application was approved.
        app(MessageController::class)->notifyApplicantApplicationDecision($superiorNo, $applicationNo, true, null);
        // Message nominees that the application was approved.
        app(MessageController::class)->notifyNomineesApplicationApproved($applicationNo);

        return response()->json(['success' => 'success'], 200);
    }

    /*
    Sets the application's status to rejected if valid
    */
    public function rejectApplication(Request $request)
    {
        $data = $request->all();
        $superiorNo = $data['accountNo'];
        $applicationNo = $data['applicationNo'];
        $rejectReason = $data['rejectReason'];

        // Check if user exists for given user id
        if (!Account::where('accountNo', $superiorNo)->first()) {
            // User does not exist, return exception
            return response()->json(['error' => 'Account does not exist.'], 500);
        }

        // Check if application exists for hte given applicationNo
        $application = Application::where('applicationNo', $applicationNo)->first();
        if (!$application) {
            return response()->json(['error' => 'Application does not exist.'], 500);
        }

        // Check that the application status is Undecided
        if ($application->status != 'U') {
            return response()->json(['error' => 'Application cannot be reviewed - nominee responses outstanding.'], 500);
        }

        // Check if the superior is the superior of the applicant
        $applicant = Account::where('accountNo', $application->accountNo)->first();
        if (!$applicant) {
            return response()->json(['error' => 'Applicant does not exist.'], 500);
        }

        // Check if the applicant's superior or expected substitute is the given superiorNo
        $expectedSubstitute = app(AccountController::class)->getCurrentLineManager($applicant->accountNo);
        if ($superiorNo != $applicant->superiorNo && $superiorNo != $expectedSubstitute->accountNo) {
            return response()->json(['error' => 'Account is not the superior of applicant.'], 500);
        }

        // Confirm that reject reason is not null or empty
        if ($rejectReason == null || $rejectReason == "") {
            return response()->json(['error' => 'Reject message cannot be empty.'], 500);
        }

        // set application status to 'N'
        $application->status = 'N';
        $application->processedBy = $superiorNo;
        $application->rejectReason = $rejectReason;
        $application->save();

        // Mark Application Awaiting Review message as acknowledged
        $message = Message::where('applicationNo', $applicationNo, "and")
            ->where('senderNo', $applicant->accountNo, "and")
            ->where('subject', "Application Awaiting Review")->first();
        if ($message != null) {
            $message->acknowledged = true;
            $message->save();
        }

        // Message applicant that their application was approved.
        app(MessageController::class)->notifyApplicantApplicationDecision($superiorNo, $applicationNo, false, $rejectReason);

        return response()->json(['success' => 'success'], 200);
    }
}
