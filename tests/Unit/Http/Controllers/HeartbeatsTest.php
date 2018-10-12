<?php

namespace Tests\Unit\Http\Controllers;

use App\Heartbeat;
use Carbon\Carbon;
use Tests\TestCase;
use GuzzleHttp\Exception\ClientException;
use JSHayes\FakeRequests\Traits\Laravel\FakeRequests;

class HeartbeatsTest extends TestCase
{
    use FakeRequests;

    /**
     * @test
     */
    public function it_returns_a_200_response_when_the_heartbeat_doesnt_exist()
    {
        $this->json('get', '/heartbeat/wat')->assertStatus(200);
    }

    /**
     * @test
     */
    public function it_returns_a_200_response_and_updates_the_last_check_in_time_of_a_healthy_heartbeat()
    {
        Carbon::setTestNow(now());

        $heartbeat = factory(Heartbeat::class)->states('past-due')->create();
        $this->fakeRequests();

        $this->json('get', "/heartbeat/{$heartbeat->id}")->assertStatus(200);

        $this->assertEquals(now(), $heartbeat->fresh()->last_check_in);
    }

    /**
     * @test
     */
    public function it_returns_a_200_response_and_sends_a_recovered_notification_for_a_missing_heartbeat()
    {
        Carbon::setTestNow(now());

        $heartbeat = factory(Heartbeat::class)->states('missing')->create();
        $expectation = $this->fakeRequests()->expects('post', config('services.slack.webhook-url'));

        $this->json('get', "/heartbeat/{$heartbeat->id}")->assertStatus(200);

        $this->assertEquals(now(), $heartbeat->fresh()->last_check_in);

        $message = json_decode((string) $expectation->getRequest()->getBody(), true);
        return str_contains($message['attachments'][0]['text'], "The Heartbeat `{$heartbeat->name}` has recovered!");
    }

    /**
     * @test
     */
    public function it_returns_a_200_response_when_it_fails_sending_the_notification()
    {
        Carbon::setTestNow(now());

        $heartbeat = factory(Heartbeat::class)->states('missing')->create();
        $expectation = $this->fakeRequests()->expects('post', config('services.slack.webhook-url'))->respondWith(404);

        $this->json('get', "/heartbeat/{$heartbeat->id}")->assertStatus(200);

        $this->assertEquals(now(), $heartbeat->fresh()->last_check_in);

        $this->assertExceptionSentToSentry(ClientException::class);
    }
}
