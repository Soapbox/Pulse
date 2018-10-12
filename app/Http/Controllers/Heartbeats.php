<?php

namespace App\Http\Controllers;

use Exception;
use App\Heartbeat;
use App\Slack\Message;
use App\Exceptions\Handler;
use Illuminate\Routing\Controller;
use App\Slack\Attachments\RecoveredHeartbeat;

class Heartbeats extends Controller
{
    public function checkIn(string $id)
    {
        $heartbeat = Heartbeat::find($id);

        if ($heartbeat) {
            $missing = $heartbeat->isMissing();
            $heartbeat->checkIn();

            if ($missing) {
                try {
                    (new Message())->attach(new RecoveredHeartbeat($heartbeat))->send();
                } catch (Exception $e) {
                    app(Handler::class)->report($e);
                }
            }
        }

        return [];
    }
}
