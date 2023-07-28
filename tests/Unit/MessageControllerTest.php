<?php

namespace Tests\Unit;

use Tests\TestCase;

class MessageControllerTest extends TestCase
{
    // Tests based off of data created in DatabaseSeeder.php for user id a000000

    public function test_api_request_for_messages_successful(): void
    {
        $response = $this->getJson('/api/messages/a000000');
        $response->assertStatus(200);
    }

    public function test_api_request_for_messages_invalid_user(): void
    {
        $response = $this->getJson('/api/messages/a1234545354356');
        $response->assertStatus(500);
    }

    public function test_api_request_for_messages_valid_length(): void
    {
        $response = $this->getJson('/api/messages/a000000');
        $array = $response->getData();
        $this->assertTrue(count($array) == 10);
    }

    public function test_api_request_for_messages_content_is_json(): void
    {
        $response = $this->getJson('/api/messages/a000000');
        $this->assertJson($response->content());
    }

    public function test_api_request_for_messages_content_is_valid(): void
    {
        $response = $this->get('/api/messages/a000000');
        $response->assertJsonStructure([
            0 => [
                'id',
                'receiver_id',
                'sender_id',
                'title',
                'content',
                'is_nominated_multiple',
                'acknowledged'
            ],
        ]);

        $array = json_decode($response->content());
        foreach ($array as $message) {
            $this->assertTrue($message->receiver_id == 'a000000');
        }
    }
}
