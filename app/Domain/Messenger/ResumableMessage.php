<?php

namespace Ds\Domain\Messenger;

use BotMan\BotMan\Messages\Incoming\IncomingMessage;

class ResumableMessage extends IncomingMessage
{
    /**
     * Set the recipient.
     *
     * @param string $recipient
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;
    }
}
