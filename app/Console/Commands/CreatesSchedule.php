<?php

namespace App\Console\Commands;

use Exception;
use App\Schedules\Schedule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

trait CreatesSchedule
{
    /**
     * Prompt for the amount of leeway to provide before warning about a missing heartbeat
     *
     * @return int
     */
    private function getLeeway(): int
    {
        return tap($this->ask('How much leeway should be given, in seconds?', 60), function ($leeway) {
            Validator::make(['leeway' => $leeway], ['leeway' => 'required|int|min:0'])->validate();
        });
    }

    /**
     * Prompt for the amount of minutes
     *
     * @return int
     */
    private function getMinutes(): int
    {
        return tap($this->ask('How many minutes? (1-59)', 1), function ($minutes) {
            Validator::make(['minutes' => $minutes], ['minutes' => 'required|int|between:1,59'])->validate();
        });
    }

    /**
     * Prompt for the amount of hours
     *
     * @return int
     */
    private function getHours(): int
    {
        return tap($this->ask('How many hours? (1-23)', 1), function ($hours) {
            Validator::make(['hours' => $hours], ['hours' => 'required|int|between:1,23'])->validate();
        });
    }

    /**
     * Prompt for the amount of days
     *
     * @return int
     */
    private function getDays(): int
    {
        return tap($this->ask('How many days?', 1), function ($days) {
            Validator::make(['days' => $days], ['days' => 'required|int|min:1'])->validate();
        });
    }

    /**
     * Prompt for the amount of weeks
     *
     * @return int
     */
    private function getWeeks(): int
    {
        return tap($this->ask('How many weeks?', 1), function ($weeks) {
            Validator::make(['weeks' => $weeks], ['weeks' => 'required|int|min:1'])->validate();
        });
    }

    /**
     * Prompt for the amount of months
     *
     * @return int
     */
    private function getMonths(): int
    {
        return tap($this->ask('How many months?', 1), function ($months) {
            Validator::make(['monthls' => $months], ['monthls' => 'required|int|min:1'])->validate();
        });
    }

    /**
     * Prompt for the schedule
     *
     * @return \App\Schedules\Schedule
     */
    private function getSchedule(): Schedule
    {
        $select = $this->choice('Choose a schedule type', ['Minutes', 'Hours', 'Days', 'Weeks', 'Months']);

        try {
            $class = "App\\Schedules\\$select";
            return new $class($this->{"get$select"}(), $this->getLeeway());
        } catch (ValidationException $e) {
            throw new Exception($e->validator->errors()->first());
        }
    }
}
