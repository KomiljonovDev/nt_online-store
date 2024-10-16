<?php

namespace Tests\Unit;

use App\Actions\SendTelegramMessage;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class SendTelegramMessageTest extends TestCase
{
    private string $token = '7032512823:AAFmnkUO_PDRF3xpJU70BgeL6DLqtmRTdzc';
    private string $chatId = '262247413';

    public function test_it_sends_a_message_successfully()
    {
        // Arrange: Mock the HTTP response
        Http::fake([
            'https://api.telegram.org/*' => Http::response(['ok' => true]),
        ]);

        $action = (new SendTelegramMessage());

        // Act: Invoke the action
        $action('Test message');

        // Assert: Check that the HTTP request was made
        Http::assertSent(function (Request $request) {
            return $request->url() === "https://api.telegram.org/bot{$this->token}/sendMessage" &&
                   $request['chat_id'] === $this->chatId &&
                   $request['text'] === 'Test message';
        });
    }

    public function test_it_logs_an_error_when_request_fails()
    {
        // Arrange: Mock the HTTP response to simulate a failure
        Http::fake([
            'https://api.telegram.org/*' => Http::response(null, 500),
        ]);

        Log::shouldReceive('error')->once();

        $action = new SendTelegramMessage($this->token, $this->chatId);

        // Act: Invoke the action
        $action('Test message');

        // Assert: Check that an error was logged
        Log::shouldHaveReceived('error')->once();
    }
}
