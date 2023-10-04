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
            'lName' => $attributes[2],
            'fName' => $attributes[3],
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
}
