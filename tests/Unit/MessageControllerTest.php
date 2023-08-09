<?php

namespace Tests\Unit;

use Tests\TestCase;
<<<<<<< HEAD
use App\Models\Account;
=======
>>>>>>> feature/respond-to-nominations/6
use App\Models\Message;

class MessageControllerTest extends TestCase
{
    private Account $user;

    protected function setup(): void {
        parent::setup();

        // Create test data
        $this->user = Account::factory()->create();

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

    protected function teardown(): void {       
        Message::where('receiverNo', $this->user->accountNo)->delete();
        $this->user->delete();
        parent::teardown();
    }



    public function test_api_request_for_messages_successful(): void
    {
        $response = $this->getJson("/api/messages/{$this->user->accountNo}");
        $response->assertStatus(200);
    }

    public function test_api_request_for_messages_invalid_user(): void
    {
        $response = $this->getJson('/api/messages/a1234545354356');
        $response->assertStatus(500);
    }

    public function test_api_request_for_messages_valid_length(): void
    {
        $response = $this->getJson("/api/messages/{$this->user->accountNo}");
        $array = $response->getData();
<<<<<<< HEAD
        $this->assertTrue(count($array) == 11);
=======
        $this->assertTrue(count($array) == count(Message::where('receiverNo', '000000a')->get()->toArray()));
>>>>>>> feature/respond-to-nominations/6
    }

    public function test_api_request_for_messages_content_is_json(): void
    {
        $response = $this->getJson("/api/messages/{$this->user->accountNo}");
        $this->assertJson($response->content());
    }

    public function test_api_request_for_messages_content_is_valid(): void
    {
        $response = $this->get("/api/messages/{$this->user->accountNo}");
        $response->assertJsonStructure([
            0 => [
                'messageId',
                'receiverNo',
                'applicationNo',
                'senderNo',
                'subject',
                'content',
                'acknowledged'
            ],
        ]);

        $array = json_decode($response->content());
        foreach ($array as $message) {
            $this->assertTrue($message->receiverNo == $this->user->accountNo);
        }
    }








    public function test_api_request_for_acknowledgeMessage_is_successful() : void {
        $message = Message::where('receiverNo', $this->user->accountNo)->first();
        $data = [
            'messageId' => $message->messageId,
            'accountNo' => $this->user->accountNo,
        ];        
        $response = $this->postJson("/api/acknowledgeMessage", $data);
        $response->assertStatus(200);
    }

    public function test_api_request_for_acknowledgeMessage_is_unsuccessful_invalid_accountNo() : void {
        $message = Message::where('receiverNo', $this->user->accountNo)->first();
        $data = [
            'messageId' => $message->messageId,
            'accountNo' => "badaccountno",
        ];        
        $response = $this->postJson("/api/acknowledgeMessage", $data);
        $response->assertStatus(500);
    }

    public function test_api_request_for_acknowledgeMessage_is_unsuccessful_invalid_messageId() : void {
        $data = [
            'messageId' => 14352345,
            'accountNo' => $this->user->accountNo,
        ];        
        $response = $this->postJson("/api/acknowledgeMessage", $data);
        $response->assertStatus(500);
    }

    public function test_api_request_for_acknowledgeMessage_is_unsuccessful_message_does_not_belong_to_user() : void {
        $data = [
            'messageId' => 1,
            'accountNo' => $this->user->accountNo,
        ];        
        $response = $this->postJson("/api/acknowledgeMessage", $data);
        $response->assertStatus(500);
    }

    public function test_api_request_for_acknowledgeMessage_is_successful_status_is_changed() : void {
        $message = Message::where('receiverNo', $this->user->accountNo, "and")
                    ->where('acknowledged', false)->first();
        $this->assertTrue($message->acknowledged == 0);

        $data = [
            'messageId' => $message->messageId,
            'accountNo' => $this->user->accountNo,
        ];        
        $response = $this->postJson("/api/acknowledgeMessage", $data);
        $response->assertStatus(200);



        $updatedMessage = Message::where('messageId', $message->messageId)->first();
        $this->assertTrue($updatedMessage->acknowledged == 1);
    }
}
