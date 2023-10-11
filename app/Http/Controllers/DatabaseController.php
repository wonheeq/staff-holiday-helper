<?php

namespace App\Http\Controllers;

use App\Jobs\SendWelcomeEmail;
use App\Models\Account;
use App\Models\AccountRole;
use App\Models\Role;
use App\Models\Unit;
use App\Models\Major;
use App\Models\Course;
use App\Models\EmailPreference;
use App\Models\School;
use App\Models\Application;
use App\Models\Nomination;
use App\Models\Message;
//use App\Models\EmailPreference;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class DatabaseController extends Controller
{
    /*
    Works out what table a new entry should be added to and calls a method to create the new row
     */
    public function addEntry(Request $request, String $accountNo)
    {
        // Check if user exists for given accountNo
        if (!Account::where('accountNo', $accountNo)->first()) {
            // User does not exist, return exception
            return response()->json(['error' => 'Account does not exist.'], 500);
        }
        else {
            // Verify that the account is a system admin account
            if (!Account::where('accountNo', $accountNo)->where('accountType', 'sysadmin')->first()) {
                // User is not a system admin, deny access to full table
                return response()->json(['error' => 'User not authorized for request.'], 500);
            }

            $data = $request->all();

            // Use 'fields' to work out which model the entry applies to.
            switch ($data['fields']) {
                case 'accountFields':
                    $response = $this->addAccount($data['newEntry'], $accountNo);
                    break;
                case 'accountRoleFields':
                    $response = $this->addAccountRole($data['newEntry'], $accountNo);
                    break;
                case 'roleFields':
                    $response = $this->addRole($data['newEntry']);
                    break;
                case 'unitFields':
                    $response = $this->addUnit($data['newEntry']);
                    break;
                case 'majorFields':
                    $response = $this->addMajor($data['newEntry']);
                    break;
                case 'courseFields':
                    $response = $this->addCourse($data['newEntry']);
                    break;
                case 'schoolFields':
                    $response = $this->addSchool($data['newEntry'], $accountNo);
                    break;
                default:
                    return response()->json(['error' => 'Could not determine db table'], 500);
            }

            return $response;
        }
    }

    /**
     * Adds new Account to database
     */
    private function addAccount(array $attributes, $adminNo) {
        //Log::info($attributes);
        //Log::info($attributes[1]['db_name']);

        // Check if system admin is allowed to create the account for the given schoolId
        $admin = Account::where('accountNo', $adminNo)->first();
        if ($admin->schoolId != '1' && $admin->schoolId != $attributes[4]['schoolId']) {
            return response()->json(['error' => 'Not authorised to create an account for the given schoolId.'], 500);
        }

        // Check that un-restricted attributes are valid

        // checking new primary key
        if (Account::where('accountNo', $attributes[0])->exists())
        {
            return response()->json(['error' => 'Account ID already in use.'], 500);
        }

        // accountNo
        if (strlen($attributes[0]) != 7 || !preg_match("/\A[0-9]{6}[a-z]{1}/i", $attributes[0])) {
            return response()->json(['error' => 'Account Number needs syntax<br />of 6 numbers followed by<br />a letter with no spaces'], 500);
        }

        // Checks if account is trying to have schoolId == 1
        /*if ($attributes[4]['schoolId'] == 1) {
        // The account is allowed to have a schoolId of one only if it is a 'sysadmin' and no other account is using '1' as its schoolId
            if ($attributes[1]['db_name'] != 'sysadmin' || Account::where('schoolId', 1)->exists()) {
                return response()->json(['error' => 'School Code of \'1\' can<br />only be assigned to one<br />System Administrator type<br />account at any given time.'], 500);
            }
        }*/

        $password = fake()->regexify('[A-Za-z0-9#@$%^&*]{10,15}');
        Account::create([
            'accountNo' => $attributes[0],
            'accountType' =>  $attributes[1]['db_name'],
            'lName' => $attributes[2],
            'fName' => $attributes[3],
            'password' => Hash::make($password), // Password created randomly
            'superiorNo' => $attributes[5]['accountNo'],
            'schoolId' => $attributes[4]['schoolId']
        ]);

        EmailPreference::create([
            'accountNo' => $attributes[0],
        ]);

        $data = $attributes[0];
        SendWelcomeEmail::dispatch($data, false);

        return response()->json(['success' => 'success'], 200);
    }

    /**
     * Adds new AccountRole to database
     */
    private function addAccountRole(array $attributes, $accountNo) {
        //Log::info($attributes);

        // Check if system admin is allowed to create the account for the given schoolId
        $admin = Account::where('accountNo', $accountNo)->first();
        if ($admin->schoolId != '1' && $admin->schoolId != Account::where('accountNo', $attributes[0]['accountNo'])->first()->schoolId) {
            return response()->json(['error' => 'Not authorised to create an account role for the given account - not part of same school.'], 500);
        }
        // No unrestricted attributes to check when manually adding to accountRoles

        AccountRole::create([
            // New accountRoleId automatically generated
            'accountNo' => $attributes[0]['accountNo'],
            'roleId' =>  $attributes[1]['roleId'],
            'unitId' => $attributes[2]['unitId'],
            'majorId' => $attributes[3]['majorId'],
            'courseId' => $attributes[4]['courseId'],
            'schoolId' => $attributes[5]['schoolId']
        ]);

        return response()->json(['success' => 'success'], 200);
    }


    /**
     * Adds new Role to database
     */
    private function addRole(array $attributes) {
        //Log::info($attributes);

        // No unrestricted attributes to check when manually adding to Roles

        Role::create([
            'name' =>  $attributes[0]
        ]);

        return response()->json(['success' => 'success'], 200);
    }

    /**
     * Adds new Unit to database
     */
    private function addUnit(array $attributes) {
        //Log::info($attributes);

         // Check that un-restricted attributes are valid

        // checking new primary key
        if (Unit::where('unitId', $attributes[1])->exists())
        {
            return response()->json(['error' => 'Unit Code already in use.'], 500);
        }

        // unitId
        if (strlen($attributes[1]) != 8 || !preg_match("/\A[A-Z]{4}[0-9]{4}/", $attributes[1])) {
            return response()->json(['error' => 'Unit Code needs syntax of<br />4 capital letters followed by<br />4 numbers with no spaces'], 500);
        }

        Unit::create([
            'unitId' =>  $attributes[1],
            'name' =>  $attributes[0]
        ]);

        return response()->json(['success' => 'success'], 200);
    }

    /**
     * Adds new Major to database
     */
    private function addMajor(array $attributes) {
        //Log::info($attributes);

        // Check that un-restricted attributes are valid

        // checking new primary key
        if (Major::where('majorId', $attributes[1])->exists())
        {
            return response()->json(['error' => 'Major Code already in use.'], 500);
        }

        // majorId
        if (strlen($attributes[1]) != 10 || !preg_match("/\AMJ[A-Z]{2}-[A-Z]{5}/", $attributes[1])) {
            return response()->json(['error' => 'Major Code needs syntax<br />of "MJ" followed by 2 capital<br />letters, then a "-" followed<br /> by 5 capital letters'], 500);
        }

        Major::create([
            'majorId' =>  $attributes[1],
            'name' =>  $attributes[0]
        ]);

        return response()->json(['success' => 'success'], 200);
    }

    /**
     * Adds new Course to database
     */
    private function addCourse(array $attributes) {
        //Log::info($attributes);

        // Check that un-restricted attributes are valid

        // checking new primary key
        if (Course::where('courseId', $attributes[1])->exists())
        {
            return response()->json(['error' => 'Course Code already in use.'], 500);
        }

        // courseId
        if (strlen($attributes[1]) > 10 || !preg_match("/\A[A-Z]{1,2}-[A-Z]{4,7}/", $attributes[1])) {
            return response()->json(['error' => 'Course Code needs syntax<br />of 1 to 2 capital letters,<br />then a "-" followed by<br />4 to 7 capital letters'], 500);
        }

        Course::create([
            'courseId' =>  $attributes[1],
            'name' =>  $attributes[0]
        ]);

        return response()->json(['success' => 'success'], 200);
    }

    /**
     * Adds new School to database
     */
    private function addSchool(array $attributes, $adminNo) {
        //Log::info($attributes);
        // Check if system admin is allowed to create the school (only super admins can create new schools).
        $admin = Account::where('accountNo', $adminNo)->first();
        if ($admin->schoolId != '1') {
            return response()->json(['error' => 'Not authorised to create a school - only the super administrator can create new schools.'], 500);
        }
        // No unrestricted attributes to check when manually adding to Schools

        School::create([
            'name' =>  $attributes[0]
        ]);

        return response()->json(['success' => 'success'], 200);
    }


    /**
     * Send requested file to user
    */
    public function sendCSVTemplate(Request $request, String $accountNo, String $fileName)
    {  
        // Check if user exists for given accountNo
        if (!Account::where('accountNo', $accountNo)->first()) {
            // User does not exist, return exception
            return response()->json(['error' => 'Account does not exist.'], 500);
        }
        else {
            // Verify that the account is a system admin account
            if (!Account::where('accountNo', $accountNo)->where('accountType', 'sysadmin')->first()) {
                // User is not a system admin, deny access to full table
                return response()->json(['error' => 'User not authorized for request.'], 500);
            }

            //if (Storage::disk('public')->exists("csv_templates/$fileName")) {
                //Log::info(public_path().'/csv_templates/'. $fileName);

                $response = response()->download(public_path().'/csv_templates/'. $fileName, $fileName, ['Content-type' => 'file/csv']);
                //Log::info($response);
                return $response;
            //} else {
               // return response()->json(['error' => 'File not found'], 500);
           // }  
        }
    }


    /**
     * Send add entries to db
    */
    public function addEntriesFromCSV(Request $request, String $accountNo)
    {
        // Check if user exists for given accountNo
        if (!Account::where('accountNo', $accountNo)->first()) {
            // User does not exist, return exception
            return response()->json(['error' => 'Account does not exist.'], 500);
        }
        else {
            // Verify that the account is a system admin account
            if (!Account::where('accountNo', $accountNo)->where('accountType', 'sysadmin')->first()) {
                // User is not a system admin, deny access to full table
                return response()->json(['error' => 'User not authorized for request.'], 500);
            }

            $data = $request->all();
            //Log::info($data);
            
            if (count($data['entries']) == 0) {
                return response()->json(['error' => 'Invalid: No rows filled.'], 500);
            }

            // Use 'fields' to work out which model the entry applies to.
            switch ($data['table']) {
                case 'add_staffaccounts.csv':
                    
                    $response = $this->csvAddAccounts($data['entries'], $accountNo);
                break;
                case 'add_accountroles.csv':
                    $response = $this->csvAddAccountRoles($data['entries'], $accountNo);
                break;
                case 'add_roles.csv':
                    $response = $this->csvAddRoles($data['entries']);
                break;
                case 'add_units.csv':
                    $response = $this->csvAddUnits($data['entries']);
                break;
                case 'add_majors.csv':
                    $response = $this->csvAddMajors($data['entries']);
                break;
                case 'add_courses.csv':
                    $response = $this->csvAddCourses($data['entries']);
                break;
                case 'add_schools.csv':
                    $response = $this->csvAddSchools($data['entries'], $accountNo);
                break;
                default:
                    return response()->json(['error' => 'Could not determine db table'], 500);
            }

            return $response;
        }
    }

    /**
     * Adds new Accounts to database from array
     */
    private function csvAddAccounts(array $entries, $accountNo) {
       
        // Check that all attributes are valid (input is entirely unrestricted)
        $numEntries = count($entries);
        //Log::info($numEntries);

        $admin = Account::where('accountNo', $accountNo)->first();

        for ($i = 0; $i < $numEntries; $i++) {
            // checking new primary keys
            $curID = $entries[$i]['Account Number (Staff ID)'];
            if (Account::where('accountNo', $curID)->exists())
            {
                return response()->json(['error' => 'Account ID ' . $curID . ' already in use.'], 500);
            }

            // accountNo
            if (strlen($curID) != 7 || !preg_match("/\A[0-9]{6}[a-z]{1}/i", $curID)) {
                return response()->json(['error' => $curID . ' Invalid: Account Number needs syntax of 6 numbers followed by a letter with no spaces. Check syntax or if you didn\'t fill in an attribute.'], 500);
            }


            // accountType
            $curAttr = $entries[$i]['Account Type'];
            if ($curAttr != 'staff' && $curAttr != 'lmanager' && $curAttr != 'sysadmin') {
                return response()->json(['error' => $curID . ' Invalid: Account Type must be one of \'staff\', \'lmanager\', or \'sysadmin\'. Check syntax or if you didn\'t fill in an attribute.'], 500);
            }

            // Surname & First/Other Names
            $curAttr = $entries[$i]['Surname'];
            if (strlen($curAttr) > 20) {
                return response()->json(['error' => $curID . ' Invalid: Surname must be 20 characters or less (If surname has multiple parts add some to \'First/Other Names\' column). Check syntax or if you didn\'t fill in an attribute.'], 500);
            }

            $curAttr = $entries[$i]['First/Other Names'];
            if (strlen($curAttr) > 30) {
                return response()->json(['error' => $curID . ' Invalid: First/Other Names must be 30 characters or less. Check syntax or if you didn\'t fill in an attribute.'], 500);
            }

            // School Code
            $curAttr = $entries[$i]['School Code'];
            if (School::where('schoolId', $curAttr)->doesntExist()) {
                return response()->json(['error' => $curID . ' Invalid: School Code does not exist in database. Check syntax or if you didn\'t fill in an attribute.'], 500);
            }
            else if ($curAttr == 1) {
                return response()->json(['error' => 'School Code of \'1\' is not an allowed school code for accounts.'], 500);
            }
            else if ($admin->schoolId != $curAttr && $admin->schoolId != 1) {
                return response()->json(['error' => 'Invalid: User not admin of school: \''.$curAttr.'\', only admins of that school may add new accounts with it'], 500);
            }
            /*else if ($curAttr == 1) { // Checks if account is trying to have schoolId == 1                            
                // The account is allowed to have a schoolId of one only if it is a 'sysadmin' and no other account is using '1' as its schoolId
                if ($entries[$i]['Account Type'] != 'sysadmin' || Account::where('schoolId', 1)->exists()) {
                    return response()->json(['error' => 'School Code of \'1\' can only be assigned to one System Administrator type account at any given time.'], 500);
                }
                // Ensuring other accounts in CSV don't also have schoolId == 1
                for ($j = $i + 1; $j < $numEntries; $j++) {
                    if ($entries[$j]['School Code'] == 1) {
                        return response()->json(['error' => 'School Code of \'1\' can only be assigned to one System Administrator type account at any given time.'], 500);
                    }
                }
            }*/

            // Line Manager's ID
            $curAttr = $entries[$i]['Line Manager\'s ID'];
            if (Account::where('accountNo', $curAttr)->where('accountType', '!=', 'staff')->doesntExist()) {
                if ($curAttr != 'none') {
                    return response()->json(['error' => $curID . ' Invalid: Line Manager \'' . $curAttr . '\' does not exist in database. Check syntax or if you didn\'t fill in an attribute.'], 500);
                }
            }
        }
        
        // Adding verified entries to db
        for ($i = 0; $i < $numEntries; $i++) {
            if ($entries[$i]['Line Manager\'s ID'] == 'none') {
                $entries[$i]['Line Manager\'s ID'] = NULL;
            }

            Account::create([
                'accountNo' => $entries[$i]['Account Number (Staff ID)'],
                'accountType' =>  $entries[$i]['Account Type'],
                'lName' => $entries[$i]['Surname'],
                'fName' => $entries[$i]['First/Other Names'],
                'password' => Hash::make(fake()->regexify('[A-Za-z0-9#@$%^&*]{10,15}')), // Password created randomly
                'superiorNo' => $entries[$i]['Line Manager\'s ID'],
                'schoolId' => $entries[$i]['School Code']
            ]);

            $data = $entries[$i]['Account Number (Staff ID)'];
            SendWelcomeEmail::dispatch($data, false);

            EmailPreference::create([
                'accountNo' => $data
            ]);
        }

        return response()->json(['success' => $numEntries . ' entries added!'], 200);
    }


    /**
     * Adds new AccountRoles to database from array
     */
    private function csvAddAccountRoles(array $entries, $accountNo) {
        //Log::info($entries);

        $admin = Account::where('accountNo', $accountNo)->first();

        // Check that all attributes are valid (input is entirely unrestricted)
        $numEntries = count($entries);

        for ($i = 0; $i < $numEntries; $i++) {
            
            // Primary Key automatically created

            // accountNo
            $curAttr = $entries[$i]['Account Number'];
            if (Account::where('accountNo', $curAttr)->doesntExist()) {
                return response()->json(['error' => $curAttr . ' Invalid: Account Number does not exist in database. Check syntax or if you didn\'t fill in an attribute.'], 500);
            }

            // Checking that admin has authority of given accountNo
            if ($admin->schoolId != '1' && $admin->schoolId != Account::where('accountNo', $curAttr)->first()->schoolId) {
                return response()->json(['error' => 'Not authorised to create an account role for the given account - user not part of same school.'], 500);
            }

            // Role ID
            $curAttr = $entries[$i]['Role ID'];
            if (Role::where('roleId', $curAttr)->doesntExist()) {
                return response()->json(['error' => $curAttr . ' Invalid: Role ID does not exist in database. Check syntax or if you didn\'t fill in an attribute.'], 500);
            }

            // Unit Code
            $curAttr = $entries[$i]['Unit Code'];
            if (Unit::where('unitId', $curAttr)->doesntExist()) {
                if ($curAttr != 'none') {
                    return response()->json(['error' => $curAttr . ' Invalid: Unit Code does not exist in database. Check syntax or if you didn\'t fill in an attribute.'], 500);
                }
            }

            // Major Code
            $curAttr = $entries[$i]['Major Code'];
            if (Major::where('majorId', $curAttr)->doesntExist()) {
                if ($curAttr != 'none') {
                    return response()->json(['error' => $curAttr . ' Invalid: Major Code does not exist in database. Check syntax or if you didn\'t fill in an attribute.'], 500);
                }
            }

            // Course Code
            $curAttr = $entries[$i]['Course Code'];
            if (Course::where('courseId', $curAttr)->doesntExist()) {
                if ($curAttr != 'none') {
                    return response()->json(['error' => $curAttr . ' Invalid: Course Code does not exist in database. Check syntax or if you didn\'t fill in an attribute.'], 500);
                }
            }

            // School Code
            $curAttr = $entries[$i]['School Code'];
            if (School::where('schoolId', $curAttr)->doesntExist()) {
                return response()->json(['error' => $curAttr . ' Invalid: School Code does not exist in database. Check syntax or if you didn\'t fill in an attribute.'], 500);
            }

            // Changing 'none' to NULL
            if ($entries[$i]['Unit Code'] == 'none') {
                $entries[$i]['Unit Code'] = NULL;
            }
            if ($entries[$i]['Major Code'] == 'none') {
                $entries[$i]['Major Code'] = NULL;
            }
            if ($entries[$i]['Course Code'] == 'none') {
                $entries[$i]['Course Code'] = NULL;
            }

            // Checking if role is a duplicate
            if (AccountRole::where('accountNo', $entries[$i]['Account Number'])
                            ->where('roleId', $entries[$i]['Role ID'])
                            ->where('unitId', $entries[$i]['Unit Code'])
                            ->where('majorId', $entries[$i]['Major Code'])
                            ->where('courseId', $entries[$i]['Course Code'])
                            ->where('schoolId', $curAttr)->exists()) 
            {
                return response()->json(['error' => $curAttr . ' Invalid: Account Role already exists.'], 500);
            }
        }

        // Adding verified entries to db
        for ($i = 0; $i < $numEntries; $i++) {
            AccountRole::create([
                'accountNo' =>  $entries[$i]['Account Number'],   
                'roleId' =>  $entries[$i]['Role ID'], 
                'unitId' =>  $entries[$i]['Unit Code'], 
                'majorId' =>  $entries[$i]['Major Code'], 
                'courseId' =>  $entries[$i]['Course Code'], 
                'schoolId' =>  $entries[$i]['School Code']   
            ]);
        }

        return response()->json(['success' => $numEntries . ' entries added!'], 200);
    }

    /**
     * Adds new Roles to database from array
     */
    private function csvAddRoles(array $entries) {
        // Check that all attributes are valid (input is entirely unrestricted)
        $numEntries = count($entries);

        for ($i = 0; $i < $numEntries; $i++) {
            
            // Primary Key automatically created

            // Role Name
            $curAttr = $entries[$i]['Role Name'];
            if (strlen($curAttr) > 40) {
                return response()->json(['error' => $curAttr . ' Invalid: Role Name should be under 40 characters'], 500);
            }

            if (Role::where('name', $curAttr)->exists()) {
                return response()->json(['error' => $curAttr . ' Invalid: Role Name already in use'], 500);
            }
        }

        // Adding verified entries to db
        for ($i = 0; $i < $numEntries; $i++) {
            Role::create([
                'name' =>  $entries[$i]['Role Name']      
            ]);
        }

        return response()->json(['success' => $numEntries . ' entries added!'], 200);
    }


    /**
     * Adds new Units to database from array
     */
    private function csvAddUnits(array $entries) {
        // Check that all attributes are valid (input is entirely unrestricted)
        $numEntries = count($entries);

        for ($i = 0; $i < $numEntries; $i++) {
            
            // Checking new Primary keys
            $curID = $entries[$i]['Unit Code'];
            if (Unit::where('unitId', $curID)->exists())
            {
                return response()->json(['error' => 'Unit Code ' . $curID . ' already in use.'], 500);
            }

            // unitId
            if (strlen($curID) != 8 || !preg_match("/\A[A-Z]{4}[0-9]{4}/", $curID)) {
                return response()->json(['error' => $curID . ' Invalid: Unit Code needs syntax of 4 capital letters followed by 4 numbers with no spaces.'], 500);
            }

            // Unit Name
            $curAttr = $entries[$i]['Unit Name'];
            if (strlen($curAttr) > 60) {
                return response()->json(['error' => $curID . ' Invalid: Unit Name should be under 60 characters. Check name or if you didn\'t fill in an attribute.'], 500);
            }
        }

        // Adding verified entries to db
        for ($i = 0; $i < $numEntries; $i++) {
            Unit::create([
                'unitId' => $entries[$i]['Unit Code'],
                'name' =>  $entries[$i]['Unit Name']      
            ]);
        }

        return response()->json(['success' => $numEntries . ' entries added!'], 200);
    }


    /**
     * Adds new Majors to database from array
     */
    private function csvAddMajors(array $entries) {
        // Check that all attributes are valid (input is entirely unrestricted)
        $numEntries = count($entries);

        for ($i = 0; $i < $numEntries; $i++) {
            
            // Checking new Primary keys
            $curID = $entries[$i]['Major Code'];
            if (Major::where('majorId', $curID)->exists())
            {
                return response()->json(['error' => 'Major Code ' . $curID . ' already in use.'], 500);
            }

            // majorId
            if (strlen($curID) != 10 || !preg_match("/\AMJ[A-Z]{2}-[A-Z]{5}/", $curID)) {
                return response()->json(['error' => $curID . ' Invalid: Major Code needs syntax of "MJ" followed by 2 capital letters, then a "-" followed by 5 capital letters.'], 500);
            }

            // Major Name
            $curAttr = $entries[$i]['Major Name'];
            if (strlen($curAttr) > 60) {
                return response()->json(['error' => $curID . ' Invalid: Major Name should be under 60 characters. Check name or if you didn\'t fill in an attribute.'], 500);
            }
        }

        // Adding verified entries to db
        for ($i = 0; $i < $numEntries; $i++) {
            Major::create([
                'majorId' => $entries[$i]['Major Code'],
                'name' =>  $entries[$i]['Major Name']      
            ]);
        }

        return response()->json(['success' => $numEntries . ' entries added!'], 200);
    }


    /**
     * Adds new Courses to database from array
     */
    private function csvAddCourses(array $entries) {
        // Check that all attributes are valid (input is entirely unrestricted)
        $numEntries = count($entries);

        for ($i = 0; $i < $numEntries; $i++) {
            
            // Checking new Primary keys
            $curID = $entries[$i]['Course Code'];
            if (Course::where('courseId', $curID)->exists())
            {
                return response()->json(['error' => 'Course Code ' . $curID . ' already in use.'], 500);
            }

            // courseId
            if (strlen($curID) > 10 || !preg_match("/\A[A-Z]{1,2}-[A-Z]{4,7}/", $curID)) {
                return response()->json(['error' => $curID . ' Invalid: Course Code needs syntax of 1 to 2 capital letters, then a "-" followed by 4 to 7 capital letters'], 500);
            }

            // Course Name
            $curAttr = $entries[$i]['Course Name'];
            if (strlen($curAttr) > 60) {
                return response()->json(['error' => $curID . ' Invalid: Course Name should be under 60 characters. Check name or if you didn\'t fill in an attribute.'], 500);
            }
        }

        // Adding verified entries to db
        for ($i = 0; $i < $numEntries; $i++) {
            Course::create([
                'courseId' => $entries[$i]['Course Code'],
                'name' =>  $entries[$i]['Course Name']      
            ]);
        }

        return response()->json(['success' => $numEntries . ' entries added!'], 200);
    }


    /**
     * Adds new Schools to database from array
     */
    private function csvAddSchools(array $entries, $accountNo) {
        // Check that all attributes are valid (input is entirely unrestricted)
        $numEntries = count($entries);
        $admin = Account::where('accountNo', $accountNo)->first();

        for ($i = 0; $i < $numEntries; $i++) {

            // Only super admin can create new schools.
            if ($admin->schoolId != '1') {
                return response()->json(['error' => 'Not authorised to create a new school - Only super administrator may create a new school.'], 500);
            }
            
            // Primary Key automatically created

            // School Name
            $curAttr = $entries[$i]['School Name'];
            if (strlen($curAttr) > 70) {
                return response()->json(['error' => $curAttr . ' Invalid: School Name should be under 70 characters.'], 500);
            }

            if (School::where('name', $curAttr)->exists()) {
                return response()->json(['error' => $curAttr . ' Invalid: School Name already in use.'], 500);
            }
        }

        // Adding verified entries to db
        for ($i = 0; $i < $numEntries; $i++) {
            School::create([
                'name' =>  $entries[$i]['School Name']      
            ]);
        }

        return response()->json(['success' => $numEntries . ' entries added!'], 200);
    }

    /**
     * Removes entry from database
     */
    public function dropEntry(Request $request, String $accountNo) { 
         // Check if user exists for given accountNo
         if (!Account::where('accountNo', $accountNo)->first()) {
            // User does not exist, return exception
            return response()->json(['error' => 'Account does not exist.'], 500);
        }
        else {
            // Verify that the account is a system admin account
            if (!Account::where('accountNo', $accountNo)->where('accountType', 'sysadmin')->first()) {
                // User is not a system admin, deny access to full table
                return response()->json(['error' => 'User not authorized for request.'], 500);
            }

            $data = $request->all();
            //Log::info($data);

            // Use 'table' to work out which model the entry is being removed from.
            switch ($data['table']) {
                case 'accounts': 
                    if ($data['entryId'] == $accountNo) {
                        return response()->json(['error' => 'Blocked: Deleting your own account is not permitted.'], 500);
                    }
                    else if (Account::where('accountNo', $data['entryId'])->where('schoolId', 1)->exists()) {
                        return response()->json(['error' => 'Blocked: Deleting Super Administrator account not permitted.'], 500);
                    }
                    
                    // Removing Account
                    Account::where('superiorNo', )->touch();
                    Account::destroy($data['entryId']);
                    break;
                case 'applications':
                    Application::destroy($data['entryId']);
                    break;
                case 'nominations':
                    Nomination::where('applicationNo', $data['applicationNo'])
                              ->where('nomineeNo', $data['nomineeNo'])
                              ->where('accountRoleId', $data['accountRoleId'])->delete();
                    break;
                case 'accountRoles':
                    AccountRole::destroy($data['entryId']);
                    break;
                case 'roles':
                    Role::destroy($data['entryId']);
                    break;
                case 'units':
                    AccountRole::where('unitId', $data['entryId'])->touch();
                    Unit::destroy($data['entryId']);
                    
                    break;
                case 'majors':
                    AccountRole::where('majorId', $data['entryId'])->touch();
                    Major::destroy($data['entryId']);
                    break;
                case 'courses':
                    AccountRole::where('courseId', $data['entryId'])->touch();
                    Course::destroy($data['entryId']);
                    break;
                case 'schools':
                    if ($data['entryId'] != 1) {
                        if (Account::where('accountNo', $accountNo)->where('schoolId', $data['entryId'])->exists()) {
                            return response()->json(['error' => 'Blocked: Deleting this school would result in you own account being deleted.'], 500);
                        }
                        School::destroy($data['entryId']);
                    }
                    else {
                        return response()->json(['error' => 'Blocked: School Code \'1\' deletion is not an allowed operation.'], 500);
                    }
                    break;
                case 'messages':
                    Message::destroy($data['entryId']);
                    break;
                default:
                    return response()->json(['error' => 'Could not determine db table'], 500);
            }
  
            return response()->json(['success' => 'success'], 200);
        }  
    }

    /**
     * Edits an entry in the database
     */
    public function editEntry(Request $request, String $accountNo) { 
        // Check if user exists for given accountNo
        if (!Account::where('accountNo', $accountNo)->first()) {
           // User does not exist, return exception
           return response()->json(['error' => 'Account does not exist.'], 500);
        }
        else {
           // Verify that the account is a system admin account
           if (!Account::where('accountNo', $accountNo)->where('accountType', 'sysadmin')->first()) {
               // User is not a system admin, deny access to full table
               return response()->json(['error' => 'User not authorized for request.'], 500);
           }

           $data = $request->all();
           //Log::info($data);

           $initialEntry = $data['initialEntry'];
           $entry = $data['entry'];

           // Verifying that a change has been made
           if ($initialEntry == $entry) {
                return response()->json(['error' => 'No changes made.'], 500);
           }

            // Use 'table' to work out which model the entry is being removed from.
            if ($data['table'] === "Accounts") { 
                $response = $this->editAccount($initialEntry, $entry, $accountNo);
            }
            else if ($data['table'] === "Roles") {
                $response = $this->editRole($initialEntry, $entry);
            }
            else if ($data['table'] === "Units") {
                $response = $this->editUnit($initialEntry, $entry);
            }
            else if ($data['table'] === "Majors") {
                $response = $this->editMajor($initialEntry, $entry);
            }
            else if ($data['table'] === "Courses") {
                $response = $this->editCourse($initialEntry, $entry);
            }
            else if ($data['table'] === "Schools") {
                $response = $this->editSchool($initialEntry, $entry);
            }
            else {
                return response()->json(['error' => 'Could not determine db table.'], 500);
            }
 
           return $response;
        }  
    }

    private function editAccount(Array $initialEntry, Array $entry, String $curAccount) {
        // Checking that the admin has the right to edit the account
        if ($initialEntry['Account Type'] == 'sysadmin' && Account::where('accountNo', $curAccount)->where('schoolId', 1)->doesntExist()) {
            return response()->json(['error' => 'Invalid: Only Super Administrators can edit System Administrator accounts.'], 500);
        }
        
        // Checking if other attributes have been changed and determining their validity
        if ($initialEntry['Account Type'] != $entry['Account Type']) {
            if ($entry['Account Type'] != 'staff' && $entry['Account Type'] != 'lmanager' && $entry['Account Type'] != 'sysadmin') {
                return response()->json(['error' => 'Invalid: Account Type must be one of \'staff\', \'lmanager\', or \'sysadmin\'.'], 500);
            }
        }

        if ($initialEntry['Surname'] != $entry['Surname']) {
            if (strlen($entry['Surname']) > 20) {
                return response()->json(['error' => 'Invalid: Surname must be 20 characters or less (If surname has multiple parts add some to \'First/Other Names\' column). Check syntax or if you didn\'t fill in an attribute.'], 500);
            }
        }

        if ($initialEntry['First/Other Names'] != $entry['First/Other Names']) {
            if (strlen($entry['First/Other Names']) > 30) {
                return response()->json(['error' => 'Invalid: First/Other Names must be 30 characters or less. Check syntax or if you didn\'t fill in an attribute.'], 500);
            }
        }

        if ($initialEntry['School Code'] != $entry['School Code']) {
            if (School::where('schoolId', $entry['School Code'])->doesntExist()) {
                return response()->json(['error' => 'Invalid: School Code does not exist in database. Check syntax or if you didn\'t fill in an attribute.'], 500);
            }
            else if ($entry['School Code'] == 1) {
                // Only Super Administrator can assign schoolId of 1
                if (Account::where('accountNo', $curAccount)->where('schoolId', 1)->doesntExist()) {
                    return response()->json(['error' => 'School Code of \'1\' is not an allowed school code for accounts.'], 500);
                }
                else if ($entry['Account Type'] != 'sysadmin') {
                    // Only System Administrator accounts may be granted 'Super Administrator' status.   
                    return response()->json(['error' => 'School Code of \'1\' cannot be assigned to non-sysadmin type accounts.'], 500);
                }
            }
            else if ($initialEntry['School Code'] == 1) {
                return response()->json(['error' => 'Account with School Code of \'1\' cannot have its School Code changed (in case no accoount is left with Super Administrator rights).'], 500);
            }
        }

        if ($initialEntry['Line Manager'] != $entry['Line Manager']) {
            if ($entry['Line Manager'] == "") {
                // Line manager is null
                $entry['Line Manager'] = NULL;
            }
            else if (Account::where('accountNo', $entry['Line Manager'])->where('accountType', '!=', 'staff')->doesntExist()) {
                if ($entry['Line Manager'] != 'none') {
                    return response()->json(['error' => 'Invalid: Line Manager \'' . $entry['Line Manager'] . '\' does not exist in database. Check syntax or if you didn\'t fill in an attribute.'], 500);
                }
            }
        }

        // All checks have passed
        Account::where('accountNo', $initialEntry['Account Number'])->update([
            'accountNo' => $entry['Account Number'],
            'accountType' => $entry['Account Type'],
            'lName' => $entry['Surname'],
            'fName' => $entry['First/Other Names'],
            'superiorNo' => $entry['Line Manager'],
            'schoolId' => $entry['School Code']
        ]);
        Account::where('accountNo', $entry['Account Number'])->touch();

        // If a line manager is changed to 'staff' type
        if ($initialEntry['Account Type'] != 'staff' && $entry['Account Type'] == 'staff') {
            // Remove any instances of line manager as 'superiorNo'
            Account::where('superiorNo', $initialEntry['Account Number'])->touch();
            Account::where('superiorNo', $initialEntry['Account Number'])->update(['superiorNo' => NULL]);
        }

        return response()->json(['success' => 'success'], 200);
    }

    private function editRole(Array $initialEntry, Array $entry) {
        // Verifying new data is compliant with db syntax
        if ($initialEntry['Role ID'] != $entry['Role ID']) {
            if (!preg_match("/\A[0-9]{1,}$/", $entry['Role ID'])) {
                return response()->json(['error' => 'Invalid: Role ID should be an integer.'], 500);
            }
        }

        if ($initialEntry['Role Name'] != $entry['Role Name']) {
            if (strlen($entry['Role Name']) > 40) {
                return response()->json(['error' => $entry['Role Name'] . ' Invalid: Role Name should be under 40 characters.'], 500);
            }

            if (Role::where('name', $entry['Role Name'])->exists()) {
                return response()->json(['error' => $entry['Role Name'] . ' Invalid: Role Name already in use.'], 500);
            }
        }

        Role::where('roleId', $initialEntry['Role ID'])->update([
            'roleId' => $entry['Role ID'],
            'name' => $entry['Role Name']
        ]);
        Role::where('roleId', $initialEntry['Role ID'])->touch();

        return response()->json(['success' => 'success'], 200);
    }

    private function editUnit(Array $initialEntry, Array $entry) {
        // Verifying new data is compliant with db syntax
        if ($initialEntry['Unit Code'] != $entry['Unit Code']) {
            if (strlen($entry['Unit Code']) != 8 || !preg_match("/\A[A-Z]{4}[0-9]{4}/", $entry['Unit Code'])) {
                return response()->json(['error' => $entry['Unit Code'] . ' Invalid: Unit Code needs syntax of 4 capital letters followed by 4 numbers with no spaces.'], 500);
            }
        }

        if ($initialEntry['Unit Name'] != $entry['Unit Name']) {
            if (strlen($entry['Unit Name']) > 60) {
                return response()->json(['error' => $entry['Unit Name'] . ' Invalid: Unit Name should be under 60 characters.'], 500);
            }

            if (Unit::where('name', $entry['Unit Name'])->exists()) {
                return response()->json(['error' => $entry['Unit Name'] . ' Invalid: Unit Name already in use.'], 500);
            }
        }

        Unit::where('unitId', $initialEntry['Unit Code'])->update([
            'unitId' => $entry['Unit Code'],
            'name' => $entry['Unit Name']
        ]);
        Unit::where('unitId', $initialEntry['Unit Code'])->touch();

        return response()->json(['success' => 'success'], 200);
    }

    private function editMajor(Array $initialEntry, Array $entry) {
        // Verifying new data is compliant with db syntax
        if ($initialEntry['Major Code'] != $entry['Major Code']) {
            if (strlen($entry['Major Code']) != 10 || !preg_match("/\AMJ[A-Z]{2}-[A-Z]{5}/", $entry['Major Code'])) {
                return response()->json(['error' => $entry['Major Code'] . ' Invalid: Major Code needs syntax of "MJ" followed by 2 capital letters, then a "-" followed by 5 capital letters.'], 500);
            }
        }

        if ($initialEntry['Major Name'] != $entry['Major Name']) {
            if (strlen($entry['Major Name']) > 60) {
                return response()->json(['error' => $entry['Major Name'] . ' Invalid: Major Name should be under 60 characters.'], 500);
            }

            if (Major::where('name', $entry['Major Name'])->exists()) {
                return response()->json(['error' => $entry['Major Name'] . ' Invalid: Major Name already in use.'], 500);
            }
        }

        Major::where('majorId', $initialEntry['Major Code'])->update([
            'majorId' => $entry['Major Code'],
            'name' => $entry['Major Name']
        ]);
        Major::where('majorId', $initialEntry['Major Code'])->touch();

        return response()->json(['success' => 'success'], 200);
    }

    private function editCourse(Array $initialEntry, Array $entry) {
        // Verifying new data is compliant with db syntax
        if ($initialEntry['Course Code'] != $entry['Course Code']) {
            if (strlen($entry['Course Code']) > 10 || !preg_match("/\A[A-Z]{1,2}-[A-Z]{4,7}/", $entry['Course Code'])) {
                return response()->json(['error' => $entry['Course Code'] . ' Invalid: Course Code needs syntax of 1 to 2 capital letters, then a "-" followed by 4 to 7 capital letters.'], 500);
            }
        }

        if ($initialEntry['Course Name'] != $entry['Course Name']) {
            if (strlen($entry['Course Name']) > 60) {
                return response()->json(['error' => $entry['Course Name'] . ' Invalid: Course Name should be under 60 characters.'], 500);
            }

            if (Course::where('name', $entry['Course Name'])->exists()) {
                return response()->json(['error' => $entry['Course Name'] . ' Invalid: Course Name already in use.'], 500);
            }
        }

        Course::where('courseId', $initialEntry['Course Code'])->update([
            'courseId' => $entry['Course Code'],
            'name' => $entry['Course Name']
        ]);
        Course::where('courseId', $initialEntry['Course Code'])->touch();

        return response()->json(['success' => 'success'], 200);
    }

    private function editSchool(Array $initialEntry, Array $entry) {
        // Super Administrator 1 should be immutable
        if ($initialEntry['School Code'] == 1) {
            return response()->json(['error' => $entry['School Code'] . ' Invalid: Super Administrator School Code & Name are permanent.'], 500);
        }

        // Verifying new data is compliant with db syntax
        if ($initialEntry['School Code'] != $entry['School Code']) {
            if (!preg_match("/\A[0-9]{1,}$/", $entry['School Code'])) {
                return response()->json(['error' => $entry['School Code'] . ' Invalid: School Code should be an integer.'], 500);
            }

            if (School::where('schoolId', $entry['School Code'])->exists()) {
                return response()->json(['error' => $entry['School Code'] . ' Invalid: School Code already in use'], 500);
            }
        }

        if ($initialEntry['School Name'] != $entry['School Name']) {
            if (strlen($entry['School Name']) > 60) {
                return response()->json(['error' => $entry['School Name'] . ' Invalid: School Name should be under 60 characters'], 500);
            }

            if (School::where('name', $entry['School Name'])->exists()) {
                return response()->json(['error' => $entry['School Name'] . ' Invalid: School Name already in use'], 500);
            }
        }

        School::where('schoolId', $initialEntry['School Code'])->update([
            'schoolId' => $entry['School Code'],
            'name' => $entry['School Name']
        ]);
        School::where('schoolId', $initialEntry['School Code'])->touch();

        return response()->json(['success' => 'success'], 200);
    }
}