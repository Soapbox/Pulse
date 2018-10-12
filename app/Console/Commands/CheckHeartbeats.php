<?php

namespace App\Console\Commands;

use Exception;
use App\Heartbeat;
use App\Slack\Message;
use App\Exceptions\Handler;
use Illuminate\Console\Command;
use App\Slack\Attachments\MissingHeartbeat;

class CheckHeartbeats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'heartbeat:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if heartbeats are missing.';

    /**
     * Send a notification for a heartbeat that is past due
     */
    public function handle()
    {
        Heartbeat::pastDue()->each(function ($heartbeat) {
            try {
                (new Message())->text('<!channel> There was a problem with a heartbeat!')
                    ->attach(new MissingHeartbeat($heartbeat))
                    ->send();
                $heartbeat->markAsMissing();
            } catch (Exception $e) {
                app(Handler::class)->report($e);
            }
        });
    }
}
