<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Account;
use App\Models\Message;
use App\Notifications\NewMessages;
use Illuminate\Support\Facades\Notification;
use App\Http\Controllers\MessageController;

class MessageControllerTest extends TestCase
{
    private Account $user, $adminUser, $otherUser1, $otherUser2;

    protected function setup(): void
    {
        parent::setup();

        // Create test data
        $this->user = Account::factory()->create();

        $this->adminUser = Account::factory()->create([
            'accountType' => "sysadmin"
        ]);

        $this->otherUser1 = Account::factory()->create([
            'accountType' => "staff"
        ]);

        $this->otherUser2 = Account::factory()->create([
            'accountType' => "lmanager"
        ]);

        // create 10 messages for user
        Message::factory(10)->create([
            'receiverNo' => $this->user->accountNo,
        ]);

        // Create one message where acknowledged is false
        Message::factory()->create([
            'receiverNo' => $this->user->accountNo,
            'acknowledged' => false,
        ]);
    }

    protected function teardown(): void
    {
        Message::where('receiverNo', $this->user->accountNo)->delete();
        $this->user->delete();

        $this->adminUser->delete();
        $this->otherUser1->delete();
        $this->otherUser2->delete();

        parent::teardown();
    }



    public function test_api_request_for_messages_successful(): void
    {
        $response = $this->actingAs($this->adminUser)->getJson("/api/messages/{$this->user->accountNo}");
        $response->assertStatus(200);
    }

    public function test_api_request_for_messages_invalid_user(): void
    {
        $response = $this->getJson('/api/messages/a1234545354356');
        $response->assertStatus(401);
    }

    public function test_api_request_for_messages_valid_length(): void
    {
        $response = $this->actingAs($this->adminUser)->getJson("/api/messages/{$this->user->accountNo}");
        $array = $response->getData();
        $this->assertTrue(count($array) == count(Message::where('receiverNo', $this->user->accountNo)->get()->toArray()));
    }

    public function test_api_request_for_messages_content_is_json(): void
    {
        $response = $this->actingAs($this->adminUser)->getJson("/api/messages/{$this->user->accountNo}");
        $this->assertJson($response->content());
    }

    public function test_api_request_for_messages_content_is_valid(): void
    {
        $response = $this->actingAs($this->adminUser)->get("/api/messages/{$this->user->accountNo}");
        $response->assertJsonStructure([
            0 => [
                'messageId',
                'receiverNo',
                'applicationNo',
                'senderNo',
                'subject',
                'content',
                'acknowledged',
                'updated_at'
            ],
        ]);

        $array = json_decode($response->content());
        foreach ($array as $message) {
            $this->assertTrue($message->receiverNo == $this->user->accountNo);
        }
    }

    /**
     * Unit tests for getAllMessages
     */
    public function test_api_request_for_all_messages(): void
    {
        $response = $this->actingAs($this->adminUser)->getJson("/api/allMessages/{$this->adminUser['accountNo']}");
        $response->assertStatus(200);

        $response = $this->actingAs($this->otherUser1)->getJson("/api/allMessages/{$this->otherUser1['accountNo']}");
        $response->assertStatus(403);

        $response = $this->actingAs($this->otherUser2)->getJson("/api/allMessages/{$this->otherUser2['accountNo']}");
        $response->assertStatus(403);
    }

    public function test_api_request_for_accounts_content_is_json(): void
    {
        // Check if response is json
        $response = $this->actingAs($this->adminUser)->getJson("/api/allMessages/{$this->adminUser['accountNo']}");
        $this->assertJson($response->content());
    }


    public function test_api_request_for_acknowledgeMessage_is_successful(): void
    {
        $message = Message::where('receiverNo', $this->user->accountNo)->first();
        $data = [
            'messageId' => $message->messageId,
            'accountNo' => $this->user->accountNo,
        ];
        $response = $this->actingAs($this->adminUser)->postJson("/api/acknowledgeMessage", $data);
        $response->assertStatus(200);
    }

    public function test_api_request_for_acknowledgeMessage_is_unsuccessful_invalid_accountNo(): void
    {
        $message = Message::where('receiverNo', $this->user->accountNo)->first();
        $data = [
            'messageId' => $message->messageId,
            'accountNo' => "badaccountno",
        ];
        $response = $this->actingAs($this->adminUser)->postJson("/api/acknowledgeMessage", $data);
        $response->assertStatus(500);
    }

    public function test_api_request_for_acknowledgeMessage_is_unsuccessful_invalid_messageId(): void
    {
        $data = [
            'messageId' => 14352345,
            'accountNo' => $this->user->accountNo,
        ];
        $response = $this->actingAs($this->adminUser)->postJson("/api/acknowledgeMessage", $data);
        $response->assertStatus(500);
    }

    public function test_api_request_for_acknowledgeMessage_is_unsuccessful_message_does_not_belong_to_user(): void
    {
        $data = [
            'messageId' => 1,
            'accountNo' => $this->user->accountNo,
        ];
        $response = $this->actingAs($this->adminUser)->postJson("/api/acknowledgeMessage", $data);
        $response->assertStatus(500);
    }

    public function test_api_request_for_acknowledgeMessage_is_successful_status_is_changed(): void
    {
        $message = Message::where('receiverNo', $this->user->accountNo, "and")
            ->where('acknowledged', false)->first();
        $this->assertTrue($message->acknowledged == 0);

        $data = [
            'messageId' => $message->messageId,
            'accountNo' => $this->user->accountNo,
        ];
        $response = $this->actingAs($this->adminUser)->postJson("/api/acknowledgeMessage", $data);
        $response->assertStatus(200);



        $updatedMessage = Message::where('messageId', $message->messageId)->first();
        $this->assertTrue($updatedMessage->acknowledged == 1);
}


    public function test_daily_email_user_has_messages(): void
    {
        Notification::fake();
        $controller = new MessageController();
        $controller->sendDailyMessagesUnitTestFunction($this->user);
        Notification::assertSentTo($this->user, NewMessages::class);
    }


    public function test_daily_email_user_has_no_messages(): void
    {
        Notification::fake();
        Message::where('receiverNo', $this->user->accountNo)->delete();
        $controller = new MessageController();
        $controller->sendDailyMessagesUnitTestFunction($this->user);
        Notification::assertNotSentTo($this->user, NewMessages::class);
    }
}
