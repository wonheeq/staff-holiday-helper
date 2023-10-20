<?php

namespace Tests\Unit;

use Tests\TestCase;
use Schema;
use Artisan;

use App\Models\Account;
use App\Models\Application;
use App\Models\AccountRole;
use App\Models\Nomination;
use App\Models\Message;
use App\Models\Role;
use App\Models\Unit;
use App\Models\Major;
use App\Models\Course;
use App\Models\School;



class DatabaseSeederTest extends TestCase
{
    /**
     * Testing database is working by searching for known entities created in seeder.
     * 
     * : php artisan migrate:fresh
     * : php artisan db:seed  
     */

    public function test_migrator_has_created_tables(): void
    {
        // Ensuring migrator has been used.     
        $this->assertTrue($this->migrationsComplete());     
    }

    public function test_seeder_has_created_entries(): void
    {
        // Ensuring sedder has been used       
        $this->assertTrue($this->seedingsComplete());
    }


    public function test_database_accounts(): void
    {
        // Asserting that Syste Admin account was successfully inserted from seeder
        $this->assertDatabaseHas('accounts', [
            'accountNo' => '0000000',
            'accountType' => 'sysadmin',
            'superiorNo' => null
        ]);

        // Ensuring at least one account was given '0000000' for superiorNo
        $this->assertDatabaseHas('accounts', [
            'superiorNo' => '0000000'
        ]);
    }

    public function test_database_account_roles(): void
    {
        // Asserting that expected entry exists in account_roles
        $this->assertDatabaseHas('account_roles', [
            'accountNo' => '0000000'           
        ]);
    }

    public function test_database_applications(): void
    {
        // Asserting that expected entry exists in applications
        $this->assertDatabaseHas('applications', [
            'accountNo' => '0000000',
            'processedBy' => '0000000',
            'status' => 'Y'
        ]);
    }

    public function test_database_minor_tables(): void
    {
        // Asserting that entries are present in roles, units, majors, courses, schools
        $this->assertDatabaseHas('roles', [
            'roleId' => 1,
            'name' => 'Unit Coordinator'           
        ]);

        // units, majors and courses are entirely made with fake() and have no expected entries.
        // 'test_seeder_has_created_entries' checks if they are empty along with the other tables.

        $this->assertDatabaseHas('schools', [
            'schoolId' => 101,
            'name' => 'Curtin Medical School'           
        ]);
    }

    public function test_database_nominations(): void
    {
        // Asserting that expected entry exists in nominations
        $this->assertDatabaseHas('nominations', [
            'nomineeNo' => '0000000',
        ]);
    }

    public function test_database_messages(): void
    {
        // Asserting that expected entry exists in nominations
        $this->assertDatabaseHas('messages', [
            'receiverNo' => '0000000',
        ]);
    }

    // Checks if tables beve been created
    function migrationsComplete()
    {
        //Ensuring migrator has been used.
        if (!Schema::hasTable('accounts') || !Schema::hasTable('applications') || !Schema::hasTable('account_roles') || 
            !Schema::hasTable('nominations') || !Schema::hasTable('messages') || !Schema::hasTable('units') || !Schema::hasTable('majors') 
            || !Schema::hasTable('courses') || !Schema::hasTable('schools') || !Schema::hasTable('roles')) {
                
                // Migrations incomplete
                return false;
        }
        else {
            return true;
        }
    }

    // Checks if tables have been filled
    function seedingsComplete()
    {
        // Ensuring seeder has been used.
        if (!Account::exists() || !Application::exists() || !AccountRole::exists() || !Nomination::exists() || 
        !Message::exists() || !Role::exists() || !Unit::exists() || !Major::exists() || 
        !Course::exists() || !School::exists()) {

            // Seeding incomplete
            return false;
        }
        else {
            return true;
        }
    }
}
