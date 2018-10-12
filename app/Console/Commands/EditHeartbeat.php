<?php

namespace App\Console\Commands;

use Exception;
use App\Heartbeat;

class EditHeartbeat extends HeartbeatCommand
{
    use CreatesSchedule;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'heartbeat:edit {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Edit a heartbeat';

    /**
     * Modify an existing heartbeat
     */
    public function handle()
    {
        $name = $this->argument('name');
        if (!$heartbeat = Heartbeat::findByName($name)) {
            $this->renderError("No heartbeat with the name '$name' was found.");
            return 1;
        }

        $this->info('Changing the following heartbeat:');
        $this->renderHeartbeat($heartbeat);

        try {
            $schedule = $this->getSchedule();
        } catch (Exception $e) {
            $this->renderError($e->getMessage());
            return 1;
        }

        $heartbeat->withSchedule($schedule);

        $this->info('The updated heartbeat is:');
        $this->renderHeartbeat($heartbeat);

        if ($this->confirm('Would you like to save?')) {
            $heartbeat->save();
            $this->info('Changes saved.');
            return;
        }

        $this->warn('Changes aborted.');
    }
}
