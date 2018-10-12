<?php

namespace App\Console;

use Throwable;
use App\Exceptions\Handler;
use JSHayes\FakeRequests\ClientFactory;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        Event::macro('thenNotify', function (string $key) {
            $url = config("cron.heartbeats.$key");

            if (empty($url)) {
                return $this;
            }

            return $this->then(function () use ($url) {
                try {
                    resolve(ClientFactory::class)->make()->get($url);
                } catch (Throwable $throwable) {
                    app(Handler::class)->report($throwable);
                }
            });
        });

        $schedule->command('heartbeat:check')->everyMinute()->thenNotify('heartbeat.check');
        $schedule->command('heartbeat:missing-reminder')
            ->timezone('America/Toronto')
            ->dailyAt('09:00')
            ->thenNotify('heartbeat.missing-reminder');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
