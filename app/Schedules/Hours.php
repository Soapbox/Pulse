<?php

namespace App\Schedules;

use Carbon\Carbon;

class Hours extends Schedule
{
    /**
     * Apply this schedule to the given time
     *
     * @param \Carbon\Carbon $time
     *
     * @return \Carbon\Carbon
     */
    protected function applySchedule(Carbon $time): Carbon
    {
        return $time->addHours($this->value);
    }
}
