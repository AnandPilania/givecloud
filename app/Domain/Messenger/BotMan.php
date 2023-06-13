<?php

namespace Ds\Domain\Messenger;

use BotMan\BotMan\BotMan as BotManCore;
use BotMan\BotMan\Commands\Command;
use BotMan\BotMan\Drivers\Tests\FakeDriver;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;

class BotMan extends BotManCore
{
    /**
     * Set the message.
     *
     * @param \BotMan\BotMan\Messages\Incoming\IncomingMessage $message
     */
    public function setMessage(IncomingMessage $message)
    {
        $this->message = $message;
    }

    public function getMatchingPatterns(string $message): array
    {
        $driver = FakeDriver::create();
        $incomingMessage = new IncomingMessage($message, '', '');

        $matchingMessages = $this->conversationManager->getMatchingMessages(
            [$incomingMessage],
            $this->middleware,
            $driver->getConversationAnswer($incomingMessage),
            $driver
        );

        return array_map(function ($matchingMessage) {
            return $matchingMessage->getCommand()->getPattern();
        }, $matchingMessages);
    }

    /**
     * @param mixed $payload
     * @return mixed
     */
    public function sendPayload($payload)
    {
        $message = $this->getMessage();

        if ($this->getDriver() instanceof TwilioMessageDriver) {
            // Twilio Driver uses TwiML API when a recipient is specified
            // in order to send a message we must originate
            if ($message instanceof ResumableMessage) {
                $payload['originate'] = true;
            }

            $this->getDriver()->setFromNumber($message->getRecipient());
        }

        return parent::sendPayload($payload);
    }

    public function getCommand(): Command
    {
        return $this->command;
    }
}
