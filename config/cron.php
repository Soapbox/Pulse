<?php

return [
    'heartbeats' => [
        'heartbeat' => [
            'check' => env('CRON_HEARTBEATS_HEARTBEAT_CHECK'),
            'missing-reminder' => env('CRON_HEARTBEATS_HEARTBEAT_MISSING_REMINDER'),
        ],
    ],
];
