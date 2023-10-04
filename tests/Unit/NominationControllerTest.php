<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\NominationController;
use App\Models\Application;
use App\Models\Account;
use App\Models\AccountRole;
use App\Models\Nomination;
use App\Models\Message;
use Illuminate\Support\Facades\Log;

class NominationControllerTest extends TestCase
{
    private Account $user, $otherUser, $adminUser, $otherUser1, $otherUser2;
    private Application $application;
    private array $nominations;
    private Message $message;

    protected function setup(): void
    {
        parent::setup();

        // create temp user
        $this->user = Account::factory()->create();
        // create temp application
        $this->application = Application::factory()->create([
            'accountNo' => $this->user->accountNo,
        ]);

        $this->adminUser = Account::factory()->create([
            'accountType' => "sysadmin"
        ]);

        $this->otherUser1 = Account::factory()->create([
            'accountType' => "staff"
        ]);

        $this->otherUser2 = Account::factory()->create([
            'accountType' => "lmanager"
        ]);

        AccountRole::factory(3)->create([
            'accountNo' => $this->user->accountNo,
        ]);

        // create temp nominations
        $this->nominations = array();
        for ($i = 0; $i < 3; $i++) {
            array_push($this->nominations, Nomination::factory()->create([
                'applicationNo' => $this->application->applicationNo,
                'accountRoleId' => AccountRole::where('accountNo',  $this->user->accountNo)->pluck('accountRoleId')[$i],
            ]));
        }


        $this->otherUser = Account::factory()->create();

        $otherAccountRoles = AccountRole::factory(5)->create([
            'accountNo' => $this->otherUser->accountNo,
        ]);

        // create 2 applications where the test user is nominated for multiple
        $nomMultiApps = Application::factory(2)->create([
            'accountNo' => $this->otherUser->accountNo,
            'status' => 'P',
        ]);
        foreach ($nomMultiApps as $nomMultiApp) {
            foreach ($otherAccountRoles as $accRole) {
                Nomination::factory()->create([
                    'nomineeNo' => $this->user->accountNo,
                    'applicationNo' => $nomMultiApp->applicationNo,
                    'accountRoleId' => $accRole->accountRoleId,
                    'status' => 'U',
                ]);
            }

            // create message for this application
            $this->message = Message::factory()->create([
                'applicationNo' => $nomMultiApp->applicationNo,
                'receiverNo' => $this->user->accountNo,
                'senderNo' => $this->otherUser->accountNo,
                'subject' => 'Substitution Request',
                'content' => json_encode([
                    '(testing) You have been nominated for 5 roles:' . strval($nomMultiApp->applicationNo),
                    "ROLENAME 1",
                    "ROLENAME 2",
                    "ROLENAME 3",
                    "ROLENAME 4",
                    "ROLENAME 5",
                    "Duration: {$nomMultiApp['sDate']->format('Y-m-d H:i')} - {$nomMultiApp['eDate']->format('Y-m-d H:i')}",
                ]),
                'acknowledged' => false
            ]);
        }


        // create application where the test user is nominated for single
        $nomSingleApp = Application::factory()->create([
            'accountNo' => $this->otherUser->accountNo,
            'status' => 'P',
        ]);
        Nomination::factory()->create([
            'nomineeNo' => $this->user->accountNo,
            'applicationNo' => $nomSingleApp->applicationNo,
            'accountRoleId' => $accRole->accountRoleId,
            'status' => 'U',
        ]);

        // create message for this application
        Message::factory()->create([
            'applicationNo' => $nomSingleApp->applicationNo,
            'receiverNo' => $this->user->accountNo,
            'senderNo' => $this->otherUser->accountNo,
            'subject' => 'Substitution Request',
            'content' => json_encode([
                '(testing) You have been nominated for ROLENAME',
                "Duration: {$nomSingleApp['sDate']->format('Y-m-d H:i')} - {$nomSingleApp['eDate']->format('Y-m-d H:i')}",
            ]),
            'acknowledged' => false
        ]);

        // generate "acknowledgeable" messages
        Message::factory()->create([
            'applicationNo' => null,
            'receiverNo' => $this->user->accountNo,
            'senderNo' => $this->otherUser->accountNo,
            'subject' => fake()->randomElement(["Leave Approved", "Leave Rejected"]),
            'content' => json_encode([
                'asdfasdfasdf',
            ]),
            'acknowledged' => false
        ]);
    }

    protected function teardown(): void
    {
        Message::where('senderNo', $this->otherUser->accountNo)->delete();
        Message::where('senderNo', $this->user->accountNo)->delete();
        Message::where('receiverNo', $this->otherUser->accountNo)->delete();
        Message::where('receiverNo', $this->user->accountNo)->delete();
        Nomination::where('nomineeNo',$this->user->accountNo)->delete();
        AccountRole::where('accountNo', $this->otherUser->accountNo)->delete();
        Application::where('accountNo', $this->otherUser->accountNo)->delete();
        // delete nominations then application then user
        foreach ($this->nominations as $nom) {
            Nomination::where('applicationNo', $nom->applicationNo, 'and')
                ->where('nomineeNo', $nom->nomineeNo, 'and')
                ->where('accountRoleId', $nom->accountRoleId)
                ->delete();
        }
        AccountRole::where('accountNo', $this->user->accountNo)->delete();
        $this->application->delete();
        $this->user->delete();
        $this->otherUser->delete();
        $this->adminUser->delete();
        $this->otherUser1->delete();
        $this->otherUser2->delete();
        parent::teardown();
    }

    public function test_call_getNominations_successful(): void
    {
        $result = app(NominationController::class)->getNominations($this->application->applicationNo);
        $this->assertTrue($this->nominations != null && gettype($this->nominations) == "array");
    }

    public function test_getNominations_returns_correct_amount(): void
    {
        $result = app(NominationController::class)->getNominations($this->application->applicationNo);
        $this->assertTrue(count($result) == count($this->nominations));
    }

    public function test_getNominations_content_is_valid(): void
    {
        $result = app(NominationController::class)->getNominations($this->application->applicationNo);

        // contents should match our original objects in setUp()
        $names = array();
        $nomineeNos = array();
        $statuses = array();
        foreach ($result as $index => $element) {
            $user = Account::where('accountNo', $this->nominations[$index]->nomineeNo)->first();

            array_push($names, "{$user->fName} {$user->lName}");
            array_push($nomineeNos, $user->accountNo);
            array_push($statuses, $this->nominations[$index]->status);
        }

        foreach ($result as $element) {
            $this->assertTrue(in_array($element['name'], $names));
            $this->assertTrue(in_array($element['nomineeNo'], $nomineeNos));
            $this->assertTrue(in_array($element['status'], $statuses));
        }
    }


    /**
     * Unit tests for getAllNominations
     */
    public function test_api_request_for_all_nominations(): void
    {
        $response = $this->actingAs($this->adminUser)->getJson("/api/allNominations/{$this->adminUser['accountNo']}");
        $response->assertStatus(200);

        $response = $this->actingAs($this->otherUser1)->getJson("/api/allNominations/{$this->otherUser1['accountNo']}");
        $response->assertStatus(302);

        $response = $this->actingAs($this->otherUser2)->getJson("/api/allNominations/{$this->otherUser2['accountNo']}");
        $response->assertStatus(302);
    }

    public function test_api_request_for_accounts_content_is_json(): void
    {
        // Check if response is json
        $response = $this->actingAs($this->adminUser)->getJson("/api/allNominations/{$this->adminUser['accountNo']}");
        $this->assertJson($response->content());
    }

    public function test_api_request_for_accounts_content_is_valid(): void
    {
        // Check if correct structure
        $response = $this->actingAs($this->adminUser)->getJson("/api/allNominations/{$this->adminUser['accountNo']}");
        $response->assertJsonStructure([
            0 => [
                'applicationNo',
                'nomineeNo',
                'accountRoleId',
                'status',
                'updated_at'
            ],
        ]);
    }


    public function test_acceptNominations_api_call_is_successful(): void
    {
        $response = $this->actingAs($this->adminUser)->postJson('/api/acceptNominations', [
            'messageId' => $this->message->messageId,
            'accountNo' => $this->user->accountNo,
            'applicationNo' => $this->message->applicationNo,
        ]);
        $response->assertStatus(200);
    }

    public function test_acceptNominations_changes_application_status_to_undecided_if_all_nominations_accepted(): void {
        $this->assertTrue(Application::where('applicationNo', $this->message->applicationNo)->first()->status == 'P');
        
        $response = $this->actingAs($this->adminUser)->postJson('/api/acceptNominations', [
            'messageId' => $this->message->messageId,
            'accountNo' => $this->user->accountNo,
            'applicationNo' => $this->message->applicationNo,
        ]);
        $response->assertStatus(200);

        $application = Application::where('applicationNo', $this->message->applicationNo)->first();
        $this->assertTrue($application->status == "U");
    }

    public function test_acceptNominations_api_call_is_unsuccessful_accountNo_does_not_exist(): void {
        $response = $this->actingAs($this->adminUser)->postJson('/api/acceptNominations', [
            'messageId' => $this->message->messageId,
            'accountNo' => 'asadfadf',
            'applicationNo' => $this->message->applicationNo,
        ]);
        $response->assertStatus(500);
    }

    public function test_acceptNominations_api_call_is_unsuccessful_message_does_not_exist(): void
    {
        $response = $this->actingAs($this->adminUser)->postJson('/api/acceptNominations', [
            'messageId' => 'aerioghaerfg',
            'accountNo' => $this->user->accountNo,
            'applicationNo' => $this->message->applicationNo,
        ]);
        $response->assertStatus(500);
    }

    public function test_acceptNominations_api_call_is_unsuccessful_application_does_not_exist(): void
    {
        $response = $this->actingAs($this->adminUser)->postJson('/api/acceptNominations', [
            'messageId' => $this->message->messageId,
            'accountNo' => $this->user->accountNo,
            'applicationNo' => '4315234',
        ]);
        $response->assertStatus(500);
    }

    public function test_acceptNominations_changes_all_nomination_statuses_to_accepted(): void
    {
        $response = $this->actingAs($this->adminUser)->postJson('/api/acceptNominations', [
            'messageId' => $this->message->messageId,
            'accountNo' => $this->user->accountNo,
            'applicationNo' => $this->message->applicationNo,
        ]);
        $response->assertStatus(200);

        $nominations = Nomination::where('applicationNo', $this->message->applicationNo, "and")
            ->where('nomineeNo', $this->user->accountNo)->get();

        $this->assertTrue(count($nominations->toArray()) > 0);

        foreach ($nominations as $nom) {
            $this->assertTrue($nom['status'] == 'Y');
        }
    }



    public function test_rejectNominations_api_call_is_successful(): void
    {
        $response = $this->actingAs($this->adminUser)->postJson('/api/rejectNominations', [
            'messageId' => $this->message->messageId,
            'accountNo' => $this->user->accountNo,
            'applicationNo' => $this->message->applicationNo,
        ]);
        $response->assertStatus(200);
    }

    public function test_rejectNominations_creates_message_for_applicant(): void {
        $response = $this->actingAs($this->adminUser)->postJson('/api/rejectNominations', [
            'messageId' => $this->message->messageId,
            'accountNo' => $this->user->accountNo,
            'applicationNo' => $this->message->applicationNo,
        ]);
        $response->assertStatus(200);

        $m = Message::where('receiverNo', $this->otherUser->accountNo, "and")
        ->where('senderNo', $this->user->accountNo, "and")
        ->where('subject', "Nomination/s Rejected")->first();
        $this->assertTrue($m != null);
    }

    public function test_rejectNominations_api_call_is_unsuccessful_accountNo_does_not_exist(): void {
        $response = $this->actingAs($this->adminUser)->postJson('/api/rejectNominations', [
            'messageId' => $this->message->messageId,
            'accountNo' => 'asadfadf',
            'applicationNo' => $this->message->applicationNo,
        ]);
        $response->assertStatus(500);
    }

    public function test_rejectNominations_api_call_is_unsuccessful_message_does_not_exist(): void
    {
        $response = $this->actingAs($this->adminUser)->postJson('/api/rejectNominations', [
            'messageId' => 'aerioghaerfg',
            'accountNo' => $this->user->accountNo,
            'applicationNo' => $this->message->applicationNo,
        ]);
        $response->assertStatus(500);
    }

    public function test_rejectNominations_api_call_is_unsuccessful_application_does_not_exist(): void
    {
        $response = $this->actingAs($this->adminUser)->postJson('/api/rejectNominations', [
            'messageId' => $this->message->messageId,
            'accountNo' => $this->user->accountNo,
            'applicationNo' => '4315234',
        ]);
        $response->assertStatus(500);
    }

    public function test_rejectNominations_changes_all_nomination_statuses_to_rejected(): void
    {
        $response = $this->actingAs($this->adminUser)->postJson('/api/rejectNominations', [
            'messageId' => $this->message->messageId,
            'accountNo' => $this->user->accountNo,
            'applicationNo' => $this->message->applicationNo,
        ]);
        $response->assertStatus(200);

        $nominations = Nomination::where('applicationNo', $this->message->applicationNo, "and")
            ->where('nomineeNo', $this->user->accountNo)->get();

        $this->assertTrue(count($nominations->toArray()) > 0);

        foreach ($nominations as $nom) {
            $this->assertTrue($nom['status'] == 'N');
        }
    }






    public function test_acceptSomeNominations_api_call_is_successful(): void
    {
        $nominationResponses = [];
        $nominations = Nomination::where('applicationNo', $this->message->applicationNo)
            ->where('nomineeNo', $this->user->accountNo)->get();

        // choose Y or N for nomination responses
        foreach ($nominations as $nom) {
            array_push($nominationResponses, [
                'accountRoleId' => $nom->accountRoleId,
                'status' => fake()->randomElement(['Y', 'N'])
            ]);
        }

        $response = $this->actingAs($this->adminUser)->postJson('/api/acceptSomeNominations', [
            'messageId' => $this->message->messageId,
            'accountNo' => $this->user->accountNo,
            'applicationNo' => $this->message->applicationNo,
            'responses' => $nominationResponses,
        ]);
        $response->assertStatus(200);
    }

    public function test_acceptSomeNominations_updates_application_status_if_all_nominations_have_been_accepted(): void {
        $this->assertTrue(Application::where('applicationNo', $this->message->applicationNo)->first()->status == 'P');
        $nominationResponses = [];
        $nominations = Nomination::where('applicationNo', $this->message->applicationNo)
            ->where('nomineeNo', $this->user->accountNo)->get();

        foreach ($nominations as $nom) {
            array_push($nominationResponses, [
                'accountRoleId' => $nom->accountRoleId,
                'status' => 'Y'
            ]);
        }

        $response = $this->actingAs($this->user)->postJson('/api/acceptSomeNominations', [
            'messageId' => $this->message->messageId,
            'accountNo' => $this->user->accountNo,
            'applicationNo' => $this->message->applicationNo,
            'responses' => $nominationResponses,
        ]);
        $response->assertStatus(200);

        $this->assertTrue(Application::where('applicationNo', $this->message->applicationNo)->first()->status == 'U');
    }

    public function test_acceptSomeNominations_updates_application_status_to_denied_if_any_nomination_rejected(): void {
        $this->assertTrue(Application::where('applicationNo', $this->message->applicationNo)->first()->status == 'P');
        $nominationResponses = [];
        $nominations = Nomination::where('applicationNo', $this->message->applicationNo)
            ->where('nomineeNo', $this->user->accountNo)->get();

        foreach ($nominations as $nom) {
            array_push($nominationResponses, [
                'accountRoleId' => $nom->accountRoleId,
                'status' => 'N'
            ]);
        }

        $response = $this->actingAs($this->user)->postJson('/api/acceptSomeNominations', [
            'messageId' => $this->message->messageId,
            'accountNo' => $this->user->accountNo,
            'applicationNo' => $this->message->applicationNo,
            'responses' => $nominationResponses,
        ]);
        $response->assertStatus(200);

        $this->assertTrue(Application::where('applicationNo', $this->message->applicationNo)->first()->status == 'N');

        $m = Message::where('receiverNo', $this->otherUser->accountNo, "and")
        ->where('senderNo', $this->user->accountNo, "and")
        ->where('subject', "Nomination/s Rejected")->first();
        $this->assertTrue($m != null);
    }

    public function test_acceptSomeNominations_api_call_is_unsuccessful_accountNo_does_not_exist(): void {
        $nominationResponses = [];
        $nominations = Nomination::where('applicationNo', $this->message->applicationNo)
            ->where('nomineeNo', $this->user->accountNo)->get();

        // choose Y or N for nomination responses
        foreach ($nominations as $nom) {
            array_push($nominationResponses, [
                'accountRoleId' => $nom->accountRoleId,
                'status' => fake()->randomElement(['Y', 'N'])
            ]);
        }

        $response = $this->actingAs($this->adminUser)->postJson('/api/acceptSomeNominations', [
            'messageId' => $this->message->messageId,
            'accountNo' => 'aisrogfjhapower',
            'applicationNo' => $this->message->applicationNo,
            'responses' => $nominationResponses,
        ]);
        $response->assertStatus(500);
    }

    public function test_acceptSomeNominations_api_call_is_unsuccessful_message_does_not_exist(): void
    {
        $nominationResponses = [];
        $nominations = Nomination::where('applicationNo', $this->message->applicationNo)
            ->where('nomineeNo', $this->user->accountNo)->get();

        // choose Y or N for nomination responses
        foreach ($nominations as $nom) {
            array_push($nominationResponses, [
                'accountRoleId' => $nom->accountRoleId,
                'status' => fake()->randomElement(['Y', 'N'])
            ]);
        }

        $response = $this->actingAs($this->adminUser)->postJson('/api/acceptSomeNominations', [
            'messageId' => 'asdasdasd',
            'accountNo' => $this->user->accountNo,
            'applicationNo' => $this->message->applicationNo,
            'responses' => $nominationResponses,
        ]);
        $response->assertStatus(500);
    }

    public function test_acceptSomeNominations_api_call_is_unsuccessful_application_does_not_exist(): void
    {
        $nominationResponses = [];
        $nominations = Nomination::where('applicationNo', $this->message->applicationNo)
            ->where('nomineeNo', $this->user->accountNo)->get();

        // choose Y or N for nomination responses
        foreach ($nominations as $nom) {
            array_push($nominationResponses, [
                'accountRoleId' => $nom->accountRoleId,
                'status' => fake()->randomElement(['Y', 'N'])
            ]);
        }

        $response = $this->actingAs($this->adminUser)->postJson('/api/acceptSomeNominations', [
            'messageId' => $this->message->messageId,
            'accountNo' => $this->user->accountNo,
            'applicationNo' => 'asdasddfsdf',
            'responses' => $nominationResponses,
        ]);
        $response->assertStatus(500);
    }

    public function test_acceptSomeNominations_api_call_is_unsuccessful_responses_are_null(): void
    {
        $response = $this->actingAs($this->adminUser)->postJson('/api/acceptSomeNominations', [
            'messageId' => $this->message->messageId,
            'accountNo' => $this->user->accountNo,
            'applicationNo' => $this->message->applicationNo,
            'responses' => null,
        ]);
        $response->assertStatus(500);
    }

    public function test_acceptSomeNominations_api_call_is_unsuccessful_all_nominations_have_not_been_responded_to(): void
    {
        $nominationResponses = [];
        $nominations = Nomination::where('applicationNo', $this->message->applicationNo)
            ->where('nomineeNo', $this->user->accountNo)->get();

        // choose Y or N for nomination responses
        foreach ($nominations as $nom) {
            array_push($nominationResponses, [
                'accountRoleId' => $nom->accountRoleId,
                'status' => 'U',
            ]);
        }

        $response = $this->actingAs($this->adminUser)->postJson('/api/acceptSomeNominations', [
            'messageId' => $this->message->messageId,
            'accountNo' => $this->user->accountNo,
            'applicationNo' => $this->message->applicationNo,
            'responses' => $nominationResponses,
        ]);
        $response->assertStatus(500);
    }

    public function test_acceptSomeNominations_changes_all_nomination_statuses_to_not_be_undecided(): void
    {
        $nominationResponses = [];
        $nominations = Nomination::where('applicationNo', $this->message->applicationNo)
            ->where('nomineeNo', $this->user->accountNo)->orderBy('created_at', 'DESC')->get();

        // choose Y or N for nomination responses
        $statuses = array();
        foreach ($nominations as $nom) {
            $status = fake()->randomElement(['Y', 'N']);
            array_push($nominationResponses, [
                'accountRoleId' => $nom->accountRoleId,
                'status' => $status
            ]);
            array_push($statuses, $status);
        }

        $response = $this->actingAs($this->adminUser)->postJson('/api/acceptSomeNominations', [
            'messageId' => $this->message->messageId,
            'accountNo' => $this->user->accountNo,
            'applicationNo' => $this->message->applicationNo,
            'responses' => $nominationResponses,
        ]);

        $updatedNominations = Nomination::where('applicationNo', $this->message->applicationNo, "and")
            ->where('nomineeNo', $this->user->accountNo)
            ->orderBy('created_at', 'DESC')->get();

        $this->assertTrue(count($updatedNominations->toArray()) > 0);

        $i = 0;
        foreach ($updatedNominations as $nom) {
            $this->assertTrue($nom['status'] != 'U');
            
            // Ccheck that all statuses are the same as the randomly selected ones
            $this->assertTrue($nom['status'] == $statuses[$i]);
            $i++;
        }
    }
}
