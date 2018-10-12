<?php

namespace App\Slack\Attachments;

use App\Heartbeat;
use Carbon\Carbon;
use JsonSerializable;

abstract class Attachment implements JsonSerializable
{
    private $data = [];

    /**
     * Create a new slack message attachment for the given heartbeat
     *
     * @param \App\Heartbeat $heartbeat
     */
    public function __construct(Heartbeat $heartbeat)
    {
        $missingFor = Carbon::now()->diffForHumans($heartbeat->last_check_in, true);
        $this->data['fields'] = [
            ['title' => 'Warns After', 'value' => $heartbeat->getWarnsAfter(), 'short' => true],
            ['title' => 'Missing For', 'value' => $missingFor, 'short' => true],
            ['title' => 'Last Check-In', 'value' => (string) $heartbeat->last_check_in, 'short' => true],
            ['title' => 'Expected Check-In', 'value' => (string) $heartbeat->next_check_in, 'short' => true],
        ];
    }

    /**
     * Set the text for this attachment
     *
     * @param string $text
     *
     * @return $this
     */
    protected function setText(string $text): Attachment
    {
        $this->data['text'] = $text;
        return $this;
    }

    /**
     * Set the colour for this attachment
     *
     * @param string $colour
     *
     * @return $this
     */
    protected function setColour(string $colour): Attachment
    {
        $this->data['color'] = $colour;
        return $this;
    }

    /**
     * JSON encode this attachment
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->data;
    }
}
