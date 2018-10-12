<?php

namespace Tests\Unit\Console\Commands;

use App\Heartbeat;
use Tests\TestCase;
use App\Console\Commands\CheckHeartbeats;
use GuzzleHttp\Exception\ClientException;
use JSHayes\FakeRequests\Traits\Laravel\FakeRequests;

class CheckHeartbeatsTest extends TestCase
{
    use FakeRequests;
    use InpectsSchedules;

    /**
     * @test
     */
    public function it_should_run_every_day_of_the_week()
    {
        $this->assertCommandShouldRunOn(CheckHeartbeats::class, 'sunday');
        $this->assertCommandShouldRunOn(CheckHeartbeats::class, 'monday');
        $this->assertCommandShouldRunOn(CheckHeartbeats::class, 'tuesday');
        $this->assertCommandShouldRunOn(CheckHeartbeats::class, 'wednesday');
        $this->assertCommandShouldRunOn(CheckHeartbeats::class, 'thursday');
        $this->assertCommandShouldRunOn(CheckHeartbeats::class, 'friday');
        $this->assertCommandShouldRunOn(CheckHeartbeats::class, 'saturday');
    }

    /**
     * @test
     */
    public function it_should_run_every_second()
    {
        $this->assertCommandShouldRunOn(CheckHeartbeats::class, now());
        $this->assertCommandShouldRunOn(CheckHeartbeats::class, now()->addSeconds(1));
        $this->assertCommandShouldRunOn(CheckHeartbeats::class, now()->addSeconds(2));
        $this->assertCommandShouldRunOn(CheckHeartbeats::class, now()->addSeconds(3));
        $this->assertCommandShouldRunOn(CheckHeartbeats::class, now()->addSeconds(4));
        $this->assertCommandShouldRunOn(CheckHeartbeats::class, now()->addSeconds(5));
        $this->assertCommandShouldRunOn(CheckHeartbeats::class, now()->addSeconds(6));
        $this->assertCommandShouldRunOn(CheckHeartbeats::class, now()->addSeconds(7));
        $this->assertCommandShouldRunOn(CheckHeartbeats::class, now()->addSeconds(8));
        $this->assertCommandShouldRunOn(CheckHeartbeats::class, now()->addSeconds(9));
        $this->assertCommandShouldRunOn(CheckHeartbeats::class, now()->addSeconds(10));
        $this->assertCommandShouldRunOn(CheckHeartbeats::class, now()->addSeconds(13));
        $this->assertCommandShouldRunOn(CheckHeartbeats::class, now()->addSeconds(17));
        $this->assertCommandShouldRunOn(CheckHeartbeats::class, now()->addHours(1));
    }

    /**
     * @test
     */
    public function it_send_a_notification_for_each_past_due_heartbeat()
    {
        $hb1 = factory(Heartbeat::class)->states('past-due')->create();
        $hb2 = factory(Heartbeat::class)->states('missing')->create();
        $hb3 = factory(Heartbeat::class)->states('past-due')->create();
        $hb4 = factory(Heartbeat::class)->states('healthy')->create();

        $handler = $this->fakeRequests();
        $handler->expects('post', config('services.slack.webhook-url'))->when(function ($request) use ($hb1) {
            $message = json_decode((string) $request->getBody(), true);
            return str_contains($message['attachments'][0]['text'], "`{$hb1->name}`");
        });
        $handler->expects('post', config('services.slack.webhook-url'))->when(function ($request) use ($hb3) {
            $message = json_decode((string) $request->getBody(), true);
            return str_contains($message['attachments'][0]['text'], "`{$hb3->name}`");
        });
        $this->artisan('heartbeat:check');

        $this->assertTrue($hb1->fresh()->isMissing());
        $this->assertTrue($hb2->fresh()->isMissing());
        $this->assertTrue($hb3->fresh()->isMissing());
        $this->assertFalse($hb4->fresh()->isMissing());
    }

    /**
     * @test
     */
    public function it_ignores_heartbeats_that_error_when_trying_to_send_the_notification()
    {
        $hb1 = factory(Heartbeat::class)->states('past-due')->create();
        $hb2 = factory(Heartbeat::class)->states('past-due')->create();

        $handler = $this->fakeRequests();
        $handler->expects('post', config('services.slack.webhook-url'))->respondWith(404);
        $handler->expects('post', config('services.slack.webhook-url'));
        $this->artisan('heartbeat:check');

        $heartbeats = collect([$hb1->fresh(), $hb2->fresh()]);
        $this->assertCount(1, $heartbeats->filter->isMissing());

        $this->assertExceptionSentToSentry(ClientException::class);
    }
}
