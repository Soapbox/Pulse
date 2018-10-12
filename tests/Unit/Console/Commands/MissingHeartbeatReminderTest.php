<?php

namespace Tests\Unit\Console\Commands;

use App\Heartbeat;
use Tests\TestCase;
use GuzzleHttp\Exception\ClientException;
use App\Console\Commands\MissingHeartbeatReminder;
use JSHayes\FakeRequests\Traits\Laravel\FakeRequests;

class MissingHeartbeatReminderTest extends TestCase
{
    use FakeRequests;
    use InpectsSchedules;

    /**
     * @test
     */
    public function it_should_run_every_day_of_the_week()
    {
        $start = now()->setTimezone('America/Toronto')->setTime(9, 0);
        $this->assertCommandShouldRunOn(MissingHeartbeatReminder::class, $start);
        $this->assertCommandShouldRunOn(MissingHeartbeatReminder::class, $start->addDay());
        $this->assertCommandShouldRunOn(MissingHeartbeatReminder::class, $start->addDay());
        $this->assertCommandShouldRunOn(MissingHeartbeatReminder::class, $start->addDay());
        $this->assertCommandShouldRunOn(MissingHeartbeatReminder::class, $start->addDay());
        $this->assertCommandShouldRunOn(MissingHeartbeatReminder::class, $start->addDay());
        $this->assertCommandShouldRunOn(MissingHeartbeatReminder::class, $start->addDay());
    }

    /**
     * @test
     */
    public function it_should_run_only_at_9_am_toronto_time()
    {
        $start = now()->setTimezone('America/Toronto')->setTime(9, 0);
        $this->assertCommandShouldRunOn(MissingHeartbeatReminder::class, $start);
        $this->assertCommandShouldNotRunOn(MissingHeartbeatReminder::class, $start->setTime(8, 0));
        $this->assertCommandShouldNotRunOn(MissingHeartbeatReminder::class, $start->setTime(10, 0));
        $this->assertCommandShouldNotRunOn(MissingHeartbeatReminder::class, $start->setTime(8, 59));
        $this->assertCommandShouldNotRunOn(MissingHeartbeatReminder::class, $start->setTime(9, 1));
        $this->assertCommandShouldNotRunOn(MissingHeartbeatReminder::class, $start->setTime(9, 15));
        $this->assertCommandShouldNotRunOn(MissingHeartbeatReminder::class, $start->setTime(9, 30));
        $this->assertCommandShouldNotRunOn(MissingHeartbeatReminder::class, $start->setTime(9, 45));

        $start = now()->setTimezone('UTC')->setTime(9, 0);
        $this->assertCommandShouldNotRunOn(MissingHeartbeatReminder::class, $start);
    }

    /**
     * @test
     */
    public function it_send_a_notification_for_each_past_due_heartbeat()
    {
        $hb1 = factory(Heartbeat::class)->states('past-due')->create();
        $hb2 = factory(Heartbeat::class)->states('missing')->create();
        $hb3 = factory(Heartbeat::class)->states('missing')->create();
        $hb4 = factory(Heartbeat::class)->states('healthy')->create();

        $handler = $this->fakeRequests();
        $expectation = $handler->expects('post', config('services.slack.webhook-url'));
        $this->artisan('heartbeat:missing-reminder');

        $body = json_decode($expectation->getRequest()->getBody(), true);
        $this->assertCount(2, $body['attachments']);
        $this->assertNotEmpty(array_filter($body['attachments'], function ($attachment) use ($hb2) {
            return str_contains($attachment['text'], "`{$hb2->name}`");
        }));
        $this->assertNotEmpty(array_filter($body['attachments'], function ($attachment) use ($hb3) {
            return str_contains($attachment['text'], "`{$hb3->name}`");
        }));
    }

    /**
     * @test
     */
    public function it_logs_an_error_when_it_fails_sending_the_notificaiton()
    {
        $hb1 = factory(Heartbeat::class)->states('missing')->create();

        $handler = $this->fakeRequests();
        $expectation = $handler->expects('post', config('services.slack.webhook-url'))->respondWith(404);
        $this->artisan('heartbeat:missing-reminder');

        $this->assertExceptionSentToSentry(ClientException::class);
    }
}
