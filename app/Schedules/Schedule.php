<?php

namespace App\Schedules;

use Carbon\Carbon;
use Illuminate\Support\Str;

abstract class Schedule
{
    public $value;
    public $leeway;

    /**
     * Create a new schedule with the given value and leeway
     *
     * @param int $value
     * @param int $leeway
     */
    public function __construct(int $value, int $leeway)
    {
        $this->value = $value;
        $this->leeway = $leeway;
    }

    /**
     * Apply this schedule to the given time
     *
     * @param \Carbon\Carbon $time
     *
     * @return \Carbon\Carbon
     */
    abstract protected function applySchedule(Carbon $time): Carbon;

    /**
     * Get the next check in time for this schedule
     *
     * @return \Carbon\Carbon
     */
    public function getNextCheckIn(): Carbon
    {
        return $this->applySchedule(Carbon::now())->addSeconds($this->leeway);
    }

    /**
     * Get the string representation of when this schedule should warn about a missing heartbeat
     *
     * @return string
     */
    public function getWarnsAfterString(): string
    {
        $string = substr(static::class, strrpos(static::class, '\\') + 1);
        $string = $this->value == 1 ? Str::singular($string) : $string;

        return "{$this->value} $string";
    }
}
