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
            Role::factory()->raw([
                'name' => $name
            ]);
        }
        
        // Create one line manager
        $lineManager = Account::factory()->raw([
            'accountType' => 'lmanager',
            'superiorNo' => null,
        ]);

        // Create 5 accounts
        $accounts = Account::factory(5)->raw([
            'superiorNo' => $lineManager['accountNo'],
        ]);
        
        // Each account gets 3 random AccountRoles
        foreach ($accounts as $account) {
            AccountRole::factory(3)->raw([
                'accountNo' => $account['accountNo'],
            ]);
        }

        // Each account gets 4 applications
        foreach ($accounts as $account) {
            $applications = Application::factory(4)->raw([
                'accountNo' => $account['accountNo'],
                'processedBy' => $lineManager['accountNo'],
            ]);

            // Generate 4 nominations for each application
            foreach ($applications as $application) {
                // Get list of AccountRoleIds associated with applicant
                $accountRoleIds = AccountRole::where('accountNo', $account['accountNo'])->get()->pluck('accountRoleId');
                
                Nomination::factory(3)->raw([
                    'applicationNo' => $application['applicationNo'],
                    'accountRoleId' => fake()->randomElement($accountRoleIds),
                ]);
            }
        }
        
        // Generate 10 messages for each account
        foreach ($accounts as $account) {
            Message::factory(10)->raw([
                'receiverNo' => $account['accountNo'],
            ]);
        }


        // TEST USER
        $test_id = '000000a';

        Account::factory()->raw([
            'accountNo' => $test_id,
            'fName' => 'Static',
            'lName' => 'Test User',
            'password' => 'test',
            'superiorNo' => $lineManager['accountNo'],
            'remember_token' => Str::random(10),
        ]);

        // 10 roles for test user
        AccountRole::factory(10)->raw([
            'accountNo' => $test_id
        ]);

        // create 4 of each type of application status for the test user
        Application::factory()->raw([
            'accountNo' => $test_id,
            'processedBy' => $lineManager['accountNo'],
            'status' => 'Y',
        ]);
        Application::factory()->raw([
            'accountNo' => $test_id,
            'processedBy' => $lineManager['accountNo'],
            'status' => 'N',
        ]);
        Application::factory()->raw([
            'accountNo' => $test_id,
            'processedBy' => $lineManager['accountNo'],
            'status' => 'U',
        ]);
        Application::factory()->raw([
            'accountNo' => $test_id,
            'processedBy' => $lineManager['accountNo'],
            'status' => 'P',
        ]);

        $testApps = Application::where('accountNo', $test_id)->get();

        // Generate 4 nominations for each application
        foreach ($testApps as $application) {
            // Get list of AccountRoleIds associated with applicant
            $accountRoleIds = AccountRole::where('accountNo', $test_id)->get()->pluck('accountRoleId');
            
            Nomination::factory(3)->raw([
                'applicationNo' => $application['applicationNo'],
                'accountRoleId' => fake()->randomElement($accountRoleIds),
            ]);
        }

        Message::factory(10)->raw([
            'receiverNo' => $test_id,
        ]);
    }
}
