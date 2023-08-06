<?php

namespace Tests\Unit;

use Tests\TestCase;

class MessageControllerTest extends TestCase
{
    // Tests based off of data created in DatabaseSeeder.php for user id 000000a

    public function test_api_request_for_messages_successful(): void
    {
        $response = $this->getJson('/api/messages/000000a');
        $response->assertStatus(200);
    }

    public function test_api_request_for_messages_invalid_user(): void
    {
        $response = $this->getJson('/api/messages/a1234545354356');
        $response->assertStatus(500);
    }

    public function test_api_request_for_messages_valid_length(): void
    {
        $response = $this->getJson('/api/messages/000000a');
        $array = $response->getData();
        $this->assertTrue(count($array) == 10);
    }

    public function test_api_request_for_messages_content_is_json(): void
    {
        $response = $this->getJson('/api/messages/000000a');
        $this->assertJson($response->content());
    }

    public function test_api_request_for_messages_content_is_valid(): void
    {
        $response = $this->get('/api/messages/000000a');
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
            $this->assertTrue($message->receiverNo == '000000a');
        }
    }
}
