<?php

namespace Tests\Unit\Console;

use Tests\TestCase;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Console\Scheduling\Schedule;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\NullOutput;
use Illuminate\Console\Scheduling\ScheduleRunCommand;
use JSHayes\FakeRequests\Traits\Laravel\FakeRequests;

class KernelTest extends TestCase
{
    use FakeRequests;

    /**
     * @test
     */
    public function thenNotify_sends_errors_to_sentry_and_allows_other_callbacks_to_run()
    {
        config(['cron.heartbeats.test' => 'http://beats.envoyer.io/heartbeat/heart-beat-id']);

        $handler = $this->fakeRequests();
        $handler->get('http://beats.envoyer.io/heartbeat/heart-beat-id')->respondWith(404);

        $shouldBeTrue = false;

        $schedule = new Schedule();
        $schedule->command('list')
            ->thenNotify('test')
            ->then(function () use (&$shouldBeTrue) {
                $shouldBeTrue = true;
            });

        $runner = new ScheduleRunCommand($schedule);
        $runner->setLaravel($this->app);
        $runner->run(new ArgvInput([]), new NullOutput());

        $this->assertTrue($shouldBeTrue);
        $this->assertExceptionSentToSentry(ClientException::class);
    }

    /**
     * @test
     */
    public function thenNotify_does_not_send_exceptions_when_is_successfully_pings_the_heartbeat()
    {
        config(['cron.heartbeats.test' => 'http://beats.envoyer.io/heartbeat/heart-beat-id']);

        $handler = $this->fakeRequests();
        $handler->get('http://beats.envoyer.io/heartbeat/heart-beat-id');

        $shouldBeTrue = false;

        $schedule = new Schedule();
        $schedule->command('list')
            ->thenNotify('test')
            ->then(function () use (&$shouldBeTrue) {
                $shouldBeTrue = true;
            });

        $runner = new ScheduleRunCommand($schedule);
        $runner->setLaravel($this->app);
        $runner->run(new ArgvInput([]), new NullOutput());

        $this->assertTrue($shouldBeTrue);
        $this->assertNoExceptionsSentToSentry();
    }

    /**
     * @test
     */
    public function thenNotify_does_not_fail_when_given_a_null_heartbeat_url()
    {
        config(['cron.heartbeats.test' => null]);

        $handler = $this->fakeRequests();

        $shouldBeTrue = false;

        $schedule = new Schedule();
        $schedule->command('list')
            ->thenNotify('test')
            ->then(function () use (&$shouldBeTrue) {
                $shouldBeTrue = true;
            });

        $runner = new ScheduleRunCommand($schedule);
        $runner->setLaravel($this->app);
        $runner->run(new ArgvInput([]), new NullOutput());

        $this->assertTrue($shouldBeTrue);
    }
}
