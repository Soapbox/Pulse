<?php

namespace Tests\Unit\Console\Commands;

use Tests\TestCase;
use App\Schedules\Days;
use App\Schedules\Hours;
use App\Schedules\Weeks;
use App\Schedules\Months;
use App\Schedules\Minutes;

class CreateHeartbeatTest extends TestCase
{
    /**
     * @test
     */
    public function it_does_not_create_a_heartbeat_with_the_minutes_type_with_less_than_1_minute_selected()
    {
        $command = $this->artisan('heartbeat:create', ['name' => 'name']);

        $command->expectsQuestion('Choose a schedule type', 'Minutes');
        $command->expectsQuestion('How many minutes? (1-59)', 0);

        $command->run();

        $this->assertDatabaseMissing('heartbeats', ['name' => 'name']);
    }

    /**
     * @test
     */
    public function it_does_not_create_a_heartbeat_with_the_minutes_type_with_more_than_59_minutes_selected()
    {
        $command = $this->artisan('heartbeat:create', ['name' => 'name']);

        $command->expectsQuestion('Choose a schedule type', 'Minutes');
        $command->expectsQuestion('How many minutes? (1-59)', 60);

        $command->run();

        $this->assertDatabaseMissing('heartbeats', ['name' => 'name']);
    }

    /**
     * @test
     */
    public function it_creates_a_heartbeat_with_the_minutes_type_with_one_minute_selected()
    {
        $command = $this->artisan('heartbeat:create', ['name' => 'name']);

        $command->expectsQuestion('Choose a schedule type', 'Minutes');
        $command->expectsQuestion('How many minutes? (1-59)', 1);
        $command->expectsQuestion('How much leeway should be given, in seconds?', 30);

        $command->run();

        $this->assertDatabaseHas('heartbeats', [
            'name' => 'name',
            'schedule_type' => Minutes::class,
            'schedule_value' => 1,
            'schedule_leeway' => 30,
        ]);
    }

    /**
     * @test
     */
    public function it_creates_a_heartbeat_with_the_minutes_type_with_59_minutes_selected()
    {
        $command = $this->artisan('heartbeat:create', ['name' => 'name']);

        $command->expectsQuestion('Choose a schedule type', 'Minutes');
        $command->expectsQuestion('How many minutes? (1-59)', 59);
        $command->expectsQuestion('How much leeway should be given, in seconds?', 30);

        $command->run();

        $this->assertDatabaseHas('heartbeats', [
            'name' => 'name',
            'schedule_type' => Minutes::class,
            'schedule_value' => 59,
            'schedule_leeway' => 30,
        ]);
    }

    /**
     * @test
     */
    public function it_does_not_create_a_heartbeat_with_the_hours_type_with_less_than_1_hour_selected()
    {
        $command = $this->artisan('heartbeat:create', ['name' => 'name']);

        $command->expectsQuestion('Choose a schedule type', 'Hours');
        $command->expectsQuestion('How many hours? (1-23)', 0);

        $command->run();

        $this->assertDatabaseMissing('heartbeats', ['name' => 'name']);
    }

    /**
     * @test
     */
    public function it_does_not_create_a_heartbeat_with_the_hours_type_with_more_than_23_hours_selected()
    {
        $command = $this->artisan('heartbeat:create', ['name' => 'name']);

        $command->expectsQuestion('Choose a schedule type', 'Hours');
        $command->expectsQuestion('How many hours? (1-23)', 24);

        $command->run();

        $this->assertDatabaseMissing('heartbeats', ['name' => 'name']);
    }

    /**
     * @test
     */
    public function it_creates_a_heartbeat_with_the_hours_type_with_1_hour_selected()
    {
        $command = $this->artisan('heartbeat:create', ['name' => 'name']);

        $command->expectsQuestion('Choose a schedule type', 'Hours');
        $command->expectsQuestion('How many hours? (1-23)', 1);
        $command->expectsQuestion('How much leeway should be given, in seconds?', 60);

        $command->run();

        $this->assertDatabaseHas('heartbeats', [
            'name' => 'name',
            'schedule_type' => Hours::class,
            'schedule_value' => 1,
            'schedule_leeway' => 60,
        ]);
    }

    /**
     * @test
     */
    public function it_creates_a_heartbeat_with_the_hours_type_with_23_hour_selected()
    {
        $command = $this->artisan('heartbeat:create', ['name' => 'name']);

        $command->expectsQuestion('Choose a schedule type', 'Hours');
        $command->expectsQuestion('How many hours? (1-23)', 23);
        $command->expectsQuestion('How much leeway should be given, in seconds?', 60);

        $command->run();

        $this->assertDatabaseHas('heartbeats', [
            'name' => 'name',
            'schedule_type' => Hours::class,
            'schedule_value' => 23,
            'schedule_leeway' => 60,
        ]);
    }

    /**
     * @test
     */
    public function it_creates_a_heartbeat_with_the_days_type()
    {
        $command = $this->artisan('heartbeat:create', ['name' => 'name']);

        $command->expectsQuestion('Choose a schedule type', 'Days');
        $command->expectsQuestion('How many days?', 1);
        $command->expectsQuestion('How much leeway should be given, in seconds?', 60);

        $command->run();

        $this->assertDatabaseHas('heartbeats', [
            'name' => 'name',
            'schedule_type' => Days::class,
            'schedule_value' => 1,
            'schedule_leeway' => 60,
        ]);
    }

    /**
     * @test
     */
    public function it_creates_a_heartbeat_with_the_weeks_type()
    {
        $command = $this->artisan('heartbeat:create', ['name' => 'name']);

        $command->expectsQuestion('Choose a schedule type', 'Weeks');
        $command->expectsQuestion('How many weeks?', 1);
        $command->expectsQuestion('How much leeway should be given, in seconds?', 60);

        $command->run();

        $this->assertDatabaseHas('heartbeats', [
            'name' => 'name',
            'schedule_type' => Weeks::class,
            'schedule_value' => 1,
            'schedule_leeway' => 60,
        ]);
    }

    /**
     * @test
     */
    public function it_creates_a_heartbeat_with_the_months_type()
    {
        $command = $this->artisan('heartbeat:create', ['name' => 'name']);

        $command->expectsQuestion('Choose a schedule type', 'Months');
        $command->expectsQuestion('How many months?', 1);
        $command->expectsQuestion('How much leeway should be given, in seconds?', 60);

        $command->run();

        $this->assertDatabaseHas('heartbeats', [
            'name' => 'name',
            'schedule_type' => Months::class,
            'schedule_value' => 1,
            'schedule_leeway' => 60,
        ]);
    }

    /**
     * @test
     */
    public function it_does_not_create_a_heartbeat_when_the_leeway_is_less_than_0()
    {
        $command = $this->artisan('heartbeat:create', ['name' => 'name']);

        $command->expectsQuestion('Choose a schedule type', 'Months');
        $command->expectsQuestion('How many months?', 1);
        $command->expectsQuestion('How much leeway should be given, in seconds?', -1);

        $command->run();

        $this->assertDatabaseMissing('heartbeats', ['name' => 'name']);
    }

    /**
     * @test
     */
    public function it_creates_a_heartbeat_when_the_leeway_is_0()
    {
        $command = $this->artisan('heartbeat:create', ['name' => 'name']);

        $command->expectsQuestion('Choose a schedule type', 'Months');
        $command->expectsQuestion('How many months?', 1);
        $command->expectsQuestion('How much leeway should be given, in seconds?', 0);

        $command->run();

        $this->assertDatabaseHas('heartbeats', [
            'name' => 'name',
            'schedule_type' => Months::class,
            'schedule_value' => 1,
            'schedule_leeway' => 0,
        ]);
    }
}
