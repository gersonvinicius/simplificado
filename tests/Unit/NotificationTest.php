<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use App\Jobs\SendNotificationJob;

class NotificationTest extends TestCase
{
    /** @test */
    public function it_sends_notification_successfully()
    {
        Http::fake([
            'https://util.devi.tools/api/v1/notify' => Http::response(['status' => 'success'], 200),
        ]);

        $job = new SendNotificationJob('Test message', 'test@example.com');
        $job->handle();

        Http::assertSent(function ($request) {
            return $request->url() == 'https://util.devi.tools/api/v1/notify' &&
                   $request['message'] == 'Test message' &&
                   $request['to'] == 'test@example.com';
        });
    }
}

