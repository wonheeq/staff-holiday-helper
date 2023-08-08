<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Account;
use App\Models\Application;
use App\Models\Nomination;
use App\Models\AccountRole;
use App\Models\Role;
use App\Models\Message;

//use Illuminate\Support\Facades\Log;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Generate our default test roles
        $ROLE_NAMES = ["Unit Coordinator", "Major Coordinator", "Course Coordinator", "Lecturer", "Tutor"];

        foreach($ROLE_NAMES as $name) {
            Role::factory()->create([
                'name' => $name
            ]);
        }
               
        \App\Models\Unit::factory(15)->create();
        \App\Models\Course::factory(10)->create();
        \App\Models\Major::factory(10)->create();

        // schools - All 14 curtin schools shown on faculty pages on Curtin Website
        $schools = array(
            array('schoolId' => '101', 'name' => 'Curtin Medical School'), 
            array('schoolId' => '102', 'name' => 'Curtin School of Allied Health'),
            array('schoolId' => '103', 'name' => 'Curtin School of Nursing'),
            array('schoolId' => '104', 'name' => 'Curtin School of Population Health'),
            array('schoolId' => '105', 'name' => 'Curtin Business School'), 
            array('schoolId' => '106', 'name' => 'Curtin Law School'),
            array('schoolId' => '107', 'name' => 'School of Design and the Built Environment'),
            array('schoolId' => '108', 'name' => 'School of Education'),
            array('schoolId' => '109', 'name' => 'School of Media, Creative Arts and Social Inquiry'), 
            array('schoolId' => '110', 'name' => 'School of Civil and Mechanical Engineering'),
            array('schoolId' => '111', 'name' => 'School of Earth and Planetary Sciences'),
            array('schoolId' => '112', 'name' => 'School of Electrical Engineering, Computing and Mathematical Sciences'),
            array('schoolId' => '113', 'name' => 'School of Molecular and Life Sciences'), 
            array('schoolId' => '114', 'name' => 'WA School of Mines: Minerals, Energy and Chemical Engineering')
         );
        
         foreach ($schools as $school) {
            \App\Models\School::create([
              'schoolId' => $school['schoolId'],
              'name' => $school['name'],
            ]);
         }

        // Creating 5 Line Manager Accounts for other to use for superiorNo foreign key: ['112237t', '123456a', '441817e', '877873p', '000002L']
        $lineManagerNo = '000002L';
        Account::factory()->create([
            'accountNo' =>  $lineManagerNo,
            'accountType' => 'lmanager',
            'superiorNo' => null,
        ]);

        Account::factory()->create([
            'accountNo' =>  '112237t',
            'accountType' => 'lmanager',
            'superiorNo' => null,
        ]);

        Account::factory()->create([
            'accountNo' =>  '123456a',
            'accountType' => 'lmanager',
            'superiorNo' => null,
        ]);

        Account::factory()->create([
            'accountNo' =>  '441817e',
            'accountType' => 'lmanager',
            'superiorNo' => null,
        ]);

        Account::factory()->create([
            'accountNo' =>  '877873p',
            'accountType' => 'lmanager',
            'superiorNo' => null,
        ]);

        // Create 30 accounts
        Account::factory(30)->create();

        $accounts = Account::get();
        
        // Each account gets 3 random AccountRoles
        foreach ($accounts as $account) {
            AccountRole::factory(3)->create([
                'accountNo' => $account['accountNo'],
            ]);
        }

        // Each account gets 4 applications
        foreach ($accounts as $account) {
            Application::factory(4)->create([
                'accountNo' => $account['accountNo'],
                'processedBy' => $lineManagerNo,
            ]);

            $applications = Application::where('accountNo', $account['accountNo'])->get();

            // Generate 4 nominations for each application
            foreach ($applications as $application) {
                // Get list of AccountRoleIds associated with applicant
                $accountRoleIds = AccountRole::where('accountNo', $account['accountNo'])->get()->pluck('accountRoleId');
                
                Nomination::factory()->create([
                    'applicationNo' => $application['applicationNo'],
                    'accountRoleId' => $accountRoleIds[0],
                ]);
                Nomination::factory()->create([
                    'applicationNo' => $application['applicationNo'],
                    'accountRoleId' => $accountRoleIds[1],
                ]);
                Nomination::factory()->create([
                    'applicationNo' => $application['applicationNo'],
                    'accountRoleId' => $accountRoleIds[2],
                ]);
            }
        }
        
        // Generate 10 messages for each account
        foreach ($accounts as $account) {
            Message::factory(10)->create([
                'receiverNo' => $account['accountNo'],
            ]);
        }


        // TEST USER
        $test_id = '000000a';

        Account::factory()->create([
            'accountNo' => $test_id,
            'fName' => 'Static',
            'lName' => 'Test User',
            'password' => 'test',
            'superiorNo' => $lineManagerNo,
            //'remember_token' => Str::random(10),
        ]);

        // 10 roles for test user
        AccountRole::factory(10)->create([
            'accountNo' => $test_id
        ]);

        // create 4 of each type of application status for the test user
        Application::factory()->create([
            'accountNo' => $test_id,
            'processedBy' => $lineManagerNo,
            'status' => 'Y',
        ]);
        Application::factory()->create([
            'accountNo' => $test_id,
            'processedBy' => $lineManagerNo,
            'status' => 'N',
        ]);
        Application::factory()->create([
            'accountNo' => $test_id,
            'processedBy' => $lineManagerNo,
            'status' => 'U',
        ]);
        Application::factory()->create([
            'accountNo' => $test_id,
            'processedBy' => $lineManagerNo,
            'status' => 'P',
        ]);
        Application::factory()->create([
            'accountNo' => $test_id,
            'processedBy' => $lineManagerNo,
            'status' => 'C',
        ]);

        $accounts = Account::get();
        
        // Each account gets 5 random AccountRoles
        foreach ($accounts as $account) {
            AccountRole::factory(5)->create([
                'accountNo' => $account['accountNo'],
            ]);
        }

    
        // Each account gets 4 applications
        foreach ($accounts as $account) {
            Application::factory(4)->create([
                'accountNo' => $account['accountNo'],
                'processedBy' => $lineManagerNo,
            ]);

            $applications = Application::where('accountNo', $account['accountNo'], 'and')
                            ->where('accountNo', "!=", $test_id)->get();

            // Generate 5 nominations for each application
            foreach ($applications as $application) {
                // Get list of AccountRoleIds associated with applicant
                $accountRoleIds = AccountRole::where('accountNo', $account['accountNo'])->get()->pluck('accountRoleId');
                
                Nomination::factory()->create([
                    'applicationNo' => $application['applicationNo'],
                    'accountRoleId' => $accountRoleIds[0],
                ]);
                Nomination::factory()->create([
                    'applicationNo' => $application['applicationNo'],
                    'accountRoleId' => $accountRoleIds[1],
                ]);
                Nomination::factory()->create([
                    'applicationNo' => $application['applicationNo'],
                    'accountRoleId' => $accountRoleIds[2],
                ]);

                // Nominate test user
                Nomination::factory()->create([
                    'applicationNo' => $application['applicationNo'],
                    'accountRoleId' => $accountRoleIds[3],
                    'status' => 'Y',
                ]);
                Nomination::factory()->create([
                    'applicationNo' => $application['applicationNo'],
                    'accountRoleId' => $accountRoleIds[4],
                    'status' => 'Y',
                ]);
            }
        }
        
        // Generate 10 messages for each account
        foreach ($accounts as $account) {
            Message::factory(10)->create([
                'receiverNo' => $account['accountNo'],
            ]);
        }

        $testApps = Application::where('accountNo', $test_id)->get();

        // Generate 3 nominations for each application
        foreach ($testApps as $application) {
            // Get list of AccountRoleIds associated with applicant
            $accountRoleIds = AccountRole::where('accountNo', $test_id)->get()->pluck('accountRoleId');
            
            Nomination::factory()->create([
                'applicationNo' => $application['applicationNo'],
                'accountRoleId' => $accountRoleIds[0],
            ]);
            Nomination::factory()->create([
                'applicationNo' => $application['applicationNo'],
                'accountRoleId' => $accountRoleIds[1],
            ]);
            Nomination::factory()->create([
                'applicationNo' => $application['applicationNo'],
                'accountRoleId' => $accountRoleIds[2],
            ]);
        }
    }
}