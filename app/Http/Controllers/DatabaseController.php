<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountRole;
use App\Models\Role;
use App\Models\Unit;
use App\Models\Major;
use App\Models\Course;
use App\Models\School;


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
                    $response = $this->addAccount($data['newEntry']);
                    break;
                case 'accountRoleFields':
                    $response = $this->addAccountRole($data['newEntry']);
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
                    $response = $this->addSchool($data['newEntry']);
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
    private function addAccount(array $attributes) {
        //Log::info($attributes);
        //Log::info($attributes[1]['db_name']);

        // Check that un-restricted attributes are valid

        // checking new primary key
        if (Account::where('accountNo', $attributes[0])->exists())
        {
            return response()->json(['error' => 'Account ID already in use.'], 500);
        }

        // accountNo
        if (strlen($attributes[0]) != 7 || !preg_match("/\A[0-9]{6}[a-z]{1}/", $attributes[0])) {
            return response()->json(['error' => 'Account Number needs syntax<br />of 6 numbers followed by<br />lowercase letter with no spaces'], 500);
        }


        Account::create([
            'accountNo' => $attributes[0],
            'accountType' =>  $attributes[1]['db_name'],
            'lname' => $attributes[2],
            'fname' => $attributes[3],
            'password' => Hash::make(fake()->regexify('[A-Za-z0-9#@$%^&*]{10,15}')), // Password created randomly
            'superiorNo' => $attributes[5]['accountNo'],
            'schoolId' => $attributes[4]['schoolId']
        ]);

        return response()->json(['success' => 'success'], 200);
    }

    /**
     * Adds new AccountRole to database
     */
    private function addAccountRole(array $attributes) {
        //Log::info($attributes);

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
    private function addSchool(array $attributes) {
        //Log::info($attributes);

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
                    
                    $response = $this->csvAddAccounts($data['entries']);
                break;
                case 'add_accountroles.csv':
                    $response = $this->csvAddAccountRoles($data['entries']);
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
                    $response = $this->csvAddSchools($data['entries']);
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
    private function csvAddAccounts(array $entries) {
       
        // Check that all attributes are valid (input is entirely unrestricted)
        $numEntries = count($entries);
        //Log::info($numEntries);

        for ($i = 0; $i < $numEntries; $i++) {
            // checking new primary keys
            $curID = $entries[$i]['Account Number (Staff ID)'];
            if (Account::where('accountNo', $curID)->exists())
            {
                return response()->json(['error' => 'Account ID ' . $curID . ' already in use.'], 500);
            }

            // accountNo
            if (strlen($curID) != 7 || !preg_match("/\A[0-9]{6}[a-z]{1}/", $curID)) {
                return response()->json(['error' => $curID . ' Invalid: Account Number needs syntax of 6 numbers followed by lowercase letter with no spaces. Check syntax or if you didn\'t fill in an attribute.'], 500);
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
                'lname' => $entries[$i]['Surname'],
                'fname' => $entries[$i]['First/Other Names'],
                'password' => Hash::make(fake()->regexify('[A-Za-z0-9#@$%^&*]{10,15}')), // Password created randomly
                'superiorNo' => $entries[$i]['Line Manager\'s ID'],
                'schoolId' => $entries[$i]['School Code']
            ]);
        }

        return response()->json(['success' => $numEntries . ' entries added!'], 200);
    }


    /**
     * Adds new AccountRoles to database from array
     */
    private function csvAddAccountRoles(array $entries) {
        //Log::info($entries);

        // Check that all attributes are valid (input is entirely unrestricted)
        $numEntries = count($entries);

        for ($i = 0; $i < $numEntries; $i++) {
            
            // Primary Key automatically created

            // accountNo
            $curAttr = $entries[$i]['Account Number'];
            if (Account::where('accountNo', $curAttr)->doesntExist()) {
                return response()->json(['error' => $curAttr . ' Invalid: Account Number does not exist in database. Check syntax or if you didn\'t fill in an attribute.'], 500);
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
    private function csvAddSchools(array $entries) {
        // Check that all attributes are valid (input is entirely unrestricted)
        $numEntries = count($entries);

        for ($i = 0; $i < $numEntries; $i++) {
            
            // Primary Key automatically created

            // School Name
            $curAttr = $entries[$i]['School Name'];
            if (strlen($curAttr) > 70) {
                return response()->json(['error' => $curAttr . ' Invalid: School Name should be under 70 characters'], 500);
            }

            if (School::where('name', $curAttr)->exists()) {
                return response()->json(['error' => $curAttr . ' Invalid: School Name already in use'], 500);
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
}