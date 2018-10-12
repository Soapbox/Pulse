<?php

namespace App\Console\Commands;

use Exception;
use App\Heartbeat;
use App\Slack\Message;
use App\Exceptions\Handler;
use Illuminate\Console\Command;
use App\Slack\Attachments\MissingHeartbeat;

class MissingHeartbeatReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'heartbeat:missing-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if heartbeats are missing.';

    /**
     * Send a notification with all the missing heartbeats
     */
    public function handle()
    {
        $heartbeats = Heartbeat::missing()->get();

        if ($heartbeats->isEmpty()) {
            return;
        }

        $message = (new Message())->text('<!channel> Some heartbeats are still missing!');
        $heartbeats->each(function ($heartbeat) use ($message) {
            $message->attach(new MissingHeartbeat($heartbeat));
        });

        try {
            $message->send();
        } catch (Exception $e) {
            app(Handler::class)->report($e);
        }
    }
}
