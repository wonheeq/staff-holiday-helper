<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Account;
use App\Models\Application;
use App\Models\Nomination;
use App\Models\AccountRole;
use App\Models\EmailPreference;
use App\Models\Role;
use App\Models\Message;
//use App\Models\EmailPreference;
use App\Models\ReminderTimeframe;
use Illuminate\Support\Facades\Hash;

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

        foreach ($ROLE_NAMES as $name) {
            Role::factory()->create([
                'name' => $name
            ]);
        }

        \App\Models\Unit::factory(5)->create();
        \App\Models\Course::factory(5)->create();
        \App\Models\Major::factory(5)->create();

        // schools - All 14 curtin schools shown on faculty pages on Curtin Website
        $schools = array(
            array('schoolId' => '1', 'name' => 'Super Administrator'), // SchoolID: 1 reserved for Super Administrator
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
            array('schoolId' => '114', 'name' => 'WA School of Mines: Minerals, Energy and Chemical Engineering'),
        );

        foreach ($schools as $school) {
            \App\Models\School::create([
                'schoolId' => $school['schoolId'],
                'name' => $school['name'],
            ]);

            ReminderTimeframe::create([
                'schoolId' => $school['schoolId'],
                'timeframe' => '2 days',
            ]);
        }


        // Create the super admin for the system
        $superAdminNo = '0000000';
        Account::create([
            'accountNo' =>  $superAdminNo,
            'accountType' => 'sysadmin',
            'superiorNo' => null,
            'schoolId' => 1,
            'fName' => 'SUPER',
            'lName' => 'ADMIN',
            'password' => Hash::make('testPassword1'),
            'schoolId' => 1
        ]);


        // Account::factory()->create([
        //     'accountNo' =>  '112237t',
        //     'accountType' => 'lmanager',
        //     'superiorNo' => $superAdminNo,
        // ]);

        // Account::factory()->create([
        //     'accountNo' =>  '123456a',
        //     'accountType' => 'lmanager',
        //     'superiorNo' => $superAdminNo,
        // ]);

        // Account::factory()->create([
        //     'accountNo' =>  '441817e',
        //     'accountType' => 'lmanager',
        //     'superiorNo' => $superAdminNo,
        // ]);

        // Account::factory()->create([
        //     'accountNo' =>  '877873p',
        //     'accountType' => 'lmanager',
        //     'superiorNo' => $superAdminNo,
        // ]);

        // // Create test users for Hannes and co
        // $hannesAdmin = Account::factory()->create([
        //     'accountNo' => '000000X',
        //     'accountType' => 'sysadmin',
        //     'fName' => '(Admin) Hannes',
        //     'lName' => 'Herrmann',
        //     'password' => Hash::make('testPassword1'),
        //     'superiorNo' => '0000000',
        // ]);

        // $hannesManager = Account::factory()->create([
        //     'accountNo' => '000000Y',
        //     'accountType' => 'lmanager',
        //     'fName' => '(Manager) Hannes',
        //     'lName' => 'Herrmann',
        //     'password' => Hash::make('testPassword1'),
        //     'superiorNo' => $hannesAdmin->accountNo,
        // ]);

        // $hannesStaff = Account::factory()->create([
        //     'accountNo' => '000000Z',
        //     'accountType' => 'staff',
        //     'fName' => '(Staff) Hannes',
        //     'lName' => 'Herrmann',
        //     'password' => Hash::make('testPassword1'),
        //     'superiorNo' => $hannesManager->accountNo,
        // ]);


        //  // Create test users for Hannes and co
        // $ianAdmin = Account::factory()->create([
        //     'accountNo' => '000000U',
        //     'accountType' => 'sysadmin',
        //     'fName' => '(Admin) Ian',
        //     'lName' => 'van Loosen?',
        //     'password' => Hash::make('testPassword1'),
        //     'superiorNo' => '0000000',
        // ]);

        // $ianManager = Account::factory()->create([
        //     'accountNo' => '000000V',
        //     'accountType' => 'lmanager',
        //     'fName' => '(Manager) Ian',
        //     'lName' => 'van Loosen?',
        //     'password' => Hash::make('testPassword1'),
        //     'superiorNo' => $ianAdmin->accountNo,
        // ]);

        // $ianStaff = Account::factory()->create([
        //     'accountNo' => '000000W',
        //     'accountType' => 'staff',
        //     'fName' => '(Staff) Ian',
        //     'lName' => 'van Loosen?',
        //     'password' => Hash::make('testPassword1'),
        //     'superiorNo' => $ianManager->accountNo,
        // ]);

        // TEST USER
        $test_id = $superAdminNo;

        // Account::factory()->create([
        //     'accountNo' => $test_id,
        //     'accountType' => 'staff',
        //     'fName' => 'Static',
        //     'lName' => 'Test User',
        //     'password' => Hash::make('testPassword1'),
        //     'superiorNo' => $superAdminNo,
        // ]);

        // // TEST USER - sysadmin
        // Account::factory()->create([
        //     'accountNo' => '000000s',
        //     'accountType' => 'sysadmin',
        //     'fName' => 'Bhos',
        //     'lName' => 'Mann',
        //     'password' => Hash::make('testPassword1'),
        //     'superiorNo' => $superAdminNo,
        // ]);

        // 10 roles for test user
        AccountRole::factory(10)->create([
            'accountNo' => $superAdminNo
        ]);

        // create 4 of each type of application status for the test user
        Application::factory()->create([
            'accountNo' => $superAdminNo,
            'status' => 'Y',
            'processedBy' => '0000000'
        ]);
        Application::factory()->create([
            'accountNo' => $test_id,
            'status' => 'N',
        ]);
        Application::factory()->create([
            'accountNo' => $test_id,
            'status' => 'U',
        ]);
        Application::factory()->create([
            'accountNo' => $test_id,
            'status' => 'P',
        ]);
        Application::factory()->create([
            'accountNo' => $test_id,
            'status' => 'C',
        ]);

        // // Create 30 accounts
        // Account::factory(30)->create([
        //     'superiorNo' => $superAdminNo
        // ]);

        $accounts = Account::get();


        // Each Account needs and EmailPreference
        // Each account gets 5 random AccountRoles
        foreach ($accounts as $account) {
            EmailPreference::create([
                'accountNo' => $account['accountNo']
            ]);

            AccountRole::factory(5)->create([
                'accountNo' => $account['accountNo'],
            ]);
        }

        // $count = 0;
        // // 4 accounts gets 1 applications
        // foreach ($accounts as $account) {
        //     if ($count >= 4) {break;}
        //     Application::factory(1)->create([
        //         'accountNo' => $account['accountNo'],
        //         'processedBy' => $superAdminNo,
        //     ]);

        //     $applications = Application::where('accountNo', $account['accountNo'], 'and')
        //         ->where('accountNo', "!=", $test_id)->get();

        //     // Generate 5 nominations for each application
        //     foreach ($applications as $application) {
        //         // Get list of AccountRoleIds associated with applicant
        //         $accountRoleIds = AccountRole::where('accountNo', $account['accountNo'])->get()->pluck('accountRoleId');

        //         Nomination::factory()->create([
        //             'applicationNo' => $application['applicationNo'],
        //             'accountRoleId' => $accountRoleIds[0],
        //         ]);
        //         Nomination::factory()->create([
        //             'applicationNo' => $application['applicationNo'],
        //             'accountRoleId' => $accountRoleIds[1],
        //         ]);
        //         Nomination::factory()->create([
        //             'applicationNo' => $application['applicationNo'],
        //             'accountRoleId' => $accountRoleIds[2],
        //         ]);

        //         // Nominate test user
        //         Nomination::factory()->create([
        //             'applicationNo' => $application['applicationNo'],
        //             'accountRoleId' => $accountRoleIds[3],
        //             'status' => 'Y',
        //         ]);
        //         Nomination::factory()->create([
        //             'applicationNo' => $application['applicationNo'],
        //             'accountRoleId' => $accountRoleIds[4],
        //             'status' => 'Y',
        //         ]);
        //     }
        // }

        // /*
        // // Generate 10 messages for each account
        // foreach ($accounts as $account) {
        //     // ignore test id because we will generate actually working messages later
        //     // Generate simple messages that only have the option of acknowledge
        //     // Messages of subject type Substitution Request, Application Awaiting Review and etc...
        //     // will not work if they do not have the corresponding Nominations, Applications, and etc created
        //     if ($account->accountNo != $test_id) {
        //         Message::factory(10)->create([
        //             'receiverNo' => $account['accountNo'],
        //             'subject' => fake()->randomElement(["Leave Approved", "Leave Rejected"])
        //         ]);
        //     }
        // }
        // */


        // CREATE messages for 0000000 for DBSeederTest
        Message::factory(1)->create([
            'receiverNo' => "0000000",
            'subject' => fake()->randomElement(["Leave Approved", "Leave Rejected"]),
            'acknowledged' => true
        ]);



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



        // // GENERATE ACTUALLY WORKING MESSAGES


        // $otherUser = Account::factory()->create();

        // $otherAccountRoles = AccountRole::factory(5)->create([
        //     'accountNo' => $otherUser->accountNo,
        // ]);

        // // create 2 applications where the test user is nominated for multiple
        // $nomMultiApps = Application::factory(2)->create([
        //     'accountNo' => $otherUser->accountNo,
        //     'status' => 'P',
        // ]);
        // foreach ($nomMultiApps as $nomMultiApp) {
        //     foreach ($otherAccountRoles as $accRole) {
        //         Nomination::factory()->create([
        //             'nomineeNo' => $test_id,
        //             'applicationNo' => $nomMultiApp->applicationNo,
        //             'accountRoleId' => $accRole->accountRoleId,
        //             'status' => 'U',
        //         ]);
        //     }

        //     // create message for this application
        //     // Message::factory()->create([
        //     //     'applicationNo' => $nomMultiApp->applicationNo,
        //     //     'receiverNo' => $test_id,
        //     //     'senderNo' => $otherUser->accountNo,
        //     //     'subject' => 'Substitution Request',
        //     //     'content' => json_encode([
        //     //         '(testing) You have been nominated for 5 roles:' . strval($nomMultiApp->applicationNo),
        //     //         "ROLENAME 1",
        //     //         "ROLENAME 2",
        //     //         "ROLENAME 3",
        //     //         "ROLENAME 4",
        //     //         "ROLENAME 5",
        //     //         "Duration: {$nomMultiApp['sDate']->format('Y-m-d H:i')} - {$nomMultiApp['eDate']->format('Y-m-d H:i')}",
        //     //     ]),
        //     //     'acknowledged' => false
        //     // ]);
        // }


        // // create application where the test user is nominated for single
        // $nomSingleApp = Application::factory()->create([
        //     'accountNo' => $otherUser->accountNo,
        //     'status' => 'P',
        // ]);
        // Nomination::factory()->create([
        //     'nomineeNo' => $test_id,
        //     'applicationNo' => $nomSingleApp->applicationNo,
        //     'accountRoleId' => $accRole->accountRoleId,
        //     'status' => 'U',
        // ]);

        // // create message for this application
        // // Message::factory()->create([
        // //     'applicationNo' => $nomSingleApp->applicationNo,
        // //     'receiverNo' => $test_id,
        // //     'senderNo' => $otherUser->accountNo,
        // //     'subject' => 'Substitution Request',
        // //     'content' => json_encode([
        // //         '(testing) You have been nominated for ROLENAME',
        // //         "Duration: {$nomSingleApp['sDate']->format('Y-m-d H:i')} - {$nomSingleApp['eDate']->format('Y-m-d H:i')}",
        // //     ]),
        // //     'acknowledged' => false
        // // ]);

        // // generate "acknowledgeable" messages
        // // Message::factory()->create([
        // //     'applicationNo' => null,
        // //     'receiverNo' => $test_id,
        // //     'senderNo' => $otherUser->accountNo,
        // //     'subject' => fake()->randomElement(["Leave Approved", "Leave Rejected"]),
        // //     'content' => json_encode([
        // //         'asdfasdfasdf',
        // //     ]),
        // //     'acknowledged' => false
        // // ]);


        $accounts = Account::get();
        foreach($accounts as $account){
            EmailPreference::factory()->create([
                'accountNo' => $account->accountNo,
            ]);
        }
    }
}
