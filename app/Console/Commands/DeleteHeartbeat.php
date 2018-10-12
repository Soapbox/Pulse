<?php

namespace App\Console\Commands;

use App\Heartbeat;

class DeleteHeartbeat extends HeartbeatCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'heartbeat:delete {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a heartbeat';

    /**
     * Delete an existing heartbeat
     */
    public function handle()
    {
        $name = $this->argument('name');
        if (!$heartbeat = Heartbeat::findByName($name)) {
            $this->renderError("No heartbeat with the name '$name' was found.");
            return 1;
        }

        $this->renderHeartbeat($heartbeat);

        if ($this->confirm('Would you like to delete this heartbeat?')) {
            $heartbeat->delete();
            $this->info('Heartbeat deleted.');
            return;
        }

        $this->warn('Heartbeat not deleted.');
    }
}
