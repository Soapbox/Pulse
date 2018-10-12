<?php

namespace App\Console\Commands;

use App\Heartbeat;

class ListHeartbeats extends HeartbeatCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'heartbeat:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all heartbeats';

    /**
     * List all heartbeats
     */
    public function handle()
    {
        $this->renderHeartbeats(Heartbeat::all());
    }
}
