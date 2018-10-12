<?php

namespace Tests\Unit\Console\Commands;

use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Scheduling\Schedule;

trait InpectsSchedules
{
    protected function getDueEventsFor(string $command)
    {
        $name = optional(collect(Artisan::all())->whereInstanceOf($command)->first())->getName();

        return $this->app->make(Schedule::class)
            ->dueEvents($this->app)
            ->filter(function ($scheduled) use ($name) {
                return str_contains($scheduled->command, $name);
            });
    }

    private function setNow($date)
    {
        if (!$date instanceof Carbon) {
            $date = Carbon::parse($date);
        }

        Carbon::setTestNow($date->setTimezone('UTC'));
    }

    protected function assertCommandShouldRunOn(string $command, $date)
    {
        $this->setNow($date);
        $this->assertNotEmpty($this->getDueEventsFor($command), "$command was not scheduled to run at " . now());
    }

    protected function assertCommandShouldNotRunOn(string $command, $date)
    {
        $this->setNow($date);
        $this->assertEmpty($this->getDueEventsFor($command), "$command was scheduled to run at " . now());
    }
}
