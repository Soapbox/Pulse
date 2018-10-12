<?php

use App\Heartbeat;
use Faker\Generator;
use App\Schedules\Minutes;

$factory->define(Heartbeat::class, function (Generator $faker) {
    return [
        'name' => $faker->unique()->word,
        'schedule_type' => Minutes::class,
        'schedule_value' => 1,
        'schedule_leeway' => 10,
        'last_check_in' => now(),
        'next_check_in' => now(),
        'status' => 'missing',
    ];
});

$factory->state(Heartbeat::class, 'past-due', function (Generator $faker) {
    return [
        'status' => 'healthy',
        'next_check_in' => now()->subMinute(),
    ];
});

$factory->state(Heartbeat::class, 'missing', function (Generator $faker) {
    return [
        'status' => 'missing',
        'next_check_in' => now()->subMinute(),
    ];
});

$factory->state(Heartbeat::class, 'healthy', function (Generator $faker) {
    return [
        'status' => 'healthy',
        'next_check_in' => now()->addMinute(),
    ];
});
