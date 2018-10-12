<?php

namespace App\Slack\Attachments;

use App\Heartbeat;

class MissingHeartbeat extends Attachment
{
    /**
     * Create a missing heartbeat attachment
     *
     * @param \App\Heartbeat $heartbeat
     */
    public function __construct(Heartbeat $heartbeat)
    {
        $this->setText("We haven't heard from Heartbeat `$heartbeat->name` in a while!")
            ->setColour('#fb4c2f');

        parent::__construct($heartbeat);
    }
}
