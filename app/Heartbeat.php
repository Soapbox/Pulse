<?php

namespace App;

use Carbon\Carbon;
use App\Schedules\Schedule;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Heartbeat extends Model
{
    protected $guarded = [];
    protected $dates = ['last_check_in', 'next_check_in'];
    public $incrementing = false;

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = Str::random(32);
            }
        });
    }

    /**
     * Create a new Heartbeat with the given name and schedule
     *
     * @param string $name
     * @param \App\Schedules\Schedule $schedule
     *
     * @return \App\Heartbeat
     */
    public static function create(string $name, Schedule $schedule): Heartbeat
    {
        return tap(new Heartbeat(), function ($heartbeat) use ($name, $schedule) {
            $heartbeat->fill(['name' => $name])->withSchedule($schedule)->save();
        });
    }

    /**
     * Update the schedule for this heartbeat
     *
     * @param \App\Schedules\Schedule $schedule
     *
     * @return $this
     */
    public function withSchedule(Schedule $schedule): Heartbeat
    {
        $now = Carbon::now();
        return $this->fill([
            'schedule_type' => get_class($schedule),
            'schedule_value' => $schedule->value,
            'schedule_leeway' => $schedule->leeway,
            'last_check_in' => $now,
            'next_check_in' => $now,
            'status' => 'missing',
        ]);
    }

    /**
     * Find a heartbeat with the given name
     *
     * @param string $name
     *
     * @return \App\Heartbeat|null
     */
    public static function findByName(string $name): ?Heartbeat
    {
        return self::where('name', $name)->first();
    }

    /**
     * Mark this heartbeat as missing
     *
     * @return $this
     */
    public function markAsMissing(): Heartbeat
    {
        $this->status = 'missing';
        $this->save();
        return $this;
    }

    /**
     * Determine if this heartbeat is missing
     *
     * @return bool
     */
    public function isMissing(): bool
    {
        return $this->status == 'missing';
    }

    /**
     * Get the schedule for this heartbeat
     *
     * @return \App\Schedules\Schedule
     */
    public function getSchedule(): Schedule
    {
        return new $this->schedule_type($this->schedule_value, $this->schedule_leeway);
    }

    /**
     * Get the string representation of when a notification will be sent for this heratbeat missing
     *
     * @return string
     */
    public function getWarnsAfter(): string
    {
        return $this->getSchedule()->getWarnsAfterString();
    }

    /**
     * Get the amount of second that this heartbeat can be late before a notification is sent
     *
     * @return int
     */
    public function getLeeway(): int
    {
        return $this->getSchedule()->leeway;
    }

    /**
     * Get the URL for this heartbeat
     *
     * @return string
     */
    public function getUrl(): string
    {
        return app('url')->to("/heartbeat/{$this->id}");
    }

    /**
     * Update the last check in time for this heartbeat
     *
     * @return void
     */
    public function checkIn(): void
    {
        $this->fill([
            'last_check_in' => Carbon::now(),
            'next_check_in' => $this->getSchedule()->getNextCheckIn(),
            'status' => 'healthy',
        ])->save();
    }

    /**
     * Apply a scope to the given query to filter only heartbeats that are past due
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePastDue(Builder $query): Builder
    {
        return $query->where('next_check_in', '<', Carbon::now())->where('status', 'healthy');
    }

    /**
     * Apply a scope to the given query to filter only heartbeats that are missing
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMissing(Builder $query): Builder
    {
        return $query->where('status', 'missing');
    }
}
