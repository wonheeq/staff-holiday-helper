<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Unit;
use App\Models\AccountRole;
use App\Models\Account;
use App\Models\Major;
use App\Models\Course;
use App\Models\School;
use Illuminate\Support\Facades\DB;


class UnitLookupTest extends TestCase
{

    protected function setup(): void
    {
        parent::setup();

        // DB::table('units')->insert([
        //     'unitId' => 'AAAA1001',
        //     'name' => 'tempName',
        // ]);
        Unit::where('unitId', 'AAAA0000')->delete();

        Unit::create([
            'unitId' => 'AAAA0000',
            'name' => 'tempName',
        ]);

        AccountRole::create([
            'accountNo' => '000000a',
            'roleId' => 1,
            'unitId' => 'AAAA0000',
            'majorId' => fake()->randomElement(Major::pluck('majorId')),
            'courseId' => fake()->randomElement(Course::pluck('courseId')),
            'schoolId' => fake()->randomElement(School::pluck('schoolId')),
        ]);
    }

    protected function tearDown(): void
    {
        AccountRole::where([
            ['accountNo', '=', '000000a'],
            ['roleId', '=', 1],
            ['unitId', '=', 'AAAA0000']
        ])->delete();

        Unit::where('unitId', 'AAAA0000')->delete();
        parent::tearDown();
    }

    public function test_lookup_valid_unit(): void
    {
        $response = $this->post('/api/getUnitDetails', [
            'code' => 'AAAA0000'
        ])->assertStatus(200);
    }
}
