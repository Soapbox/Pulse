<?php

namespace App\Console\Commands;

use Exception;
use App\Heartbeat;

class CreateHeartbeat extends HeartbeatCommand
{
    use CreatesSchedule;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'heartbeat:create {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new heartbeat';

    /**
     * Create a heartbeat
     */
    public function handle()
    {
        $name = $this->argument('name');
        if (Heartbeat::findByName($name)) {
            $this->renderError("A heartbeat with the name '$name' already exists.");
            return 1;
        }

        try {
            $schedule = $this->getSchedule();
        } catch (Exception $e) {
            $this->renderError($e->getMessage());
            return 1;
        }

        $this->renderHeartbeat(Heartbeat::create($name, $schedule));
    }
}
