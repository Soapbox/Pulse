<?php

namespace App\Slack;

use App\Slack\Attachments\Attachment;
use JSHayes\FakeRequests\ClientFactory;

class Message
{
    private $message = [];

    /**
     * Set the test for this message
     *
     * @param string $text
     *
     * @return $this
     */
    public function text(string $text): Message
    {
        $this->message['text'] = $text;
        return $this;
    }

    /**
     * Attach the given attachment to this message
     *
     * @param \App\Slack\Attachments\Attachment $attachment
     *
     * @return $this
     */
    public function attach(Attachment $attachment): Message
    {
        $this->message['attachments'][] = $attachment;
        return $this;
    }

    /**
     * Send this message to the webhook URL
     *
     * @return void
     */
    public function send(): void
    {
        resolve(ClientFactory::class)->make()->post(config('services.slack.webhook-url'), [
            'json' => $this->message,
            'connect_timeout' => 15,
            'timeout' => 15,
        ]);
    }
}
