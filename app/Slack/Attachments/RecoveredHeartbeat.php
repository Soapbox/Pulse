<?php

namespace App\Slack\Attachments;

use App\Heartbeat;

class RecoveredHeartbeat extends Attachment
{
    /**
     * Create a recovered heartbeat attachment
     *
     * @param \App\Heartbeat $heartbeat
     */
    public function __construct(Heartbeat $heartbeat)
    {
        $this->setText("The Heartbeat `{$heartbeat->name}` has recovered!")
            ->setColour('good');

        parent::__construct($heartbeat);
    }
}
