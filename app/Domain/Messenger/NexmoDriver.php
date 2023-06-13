<?php

namespace Ds\Domain\Messenger;

use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use BotMan\Drivers\Nexmo\NexmoDriver as BotManNexmoDriver;

class NexmoDriver extends BotManNexmoDriver
{
    /**
     * @param \BotMan\BotMan\Messages\Incoming\IncomingMessage $message
     * @return \BotMan\BotMan\Messages\Incoming\Answer
     */
    public function getConversationAnswer(IncomingMessage $message)
    {
        $answerText = trim($message->getText());

        return Answer::create($message->getText())
            ->setValue($answerText)
            ->setInteractiveReply(true)
            ->setMessage($message);
    }

    /**
     * @param mixed $message
     * @param \BotMan\BotMan\Messages\Incoming\IncomingMessage $matchingMessage
     * @param array $additionalParameters
     * @return array
     */
    public function buildServicePayload($message, $matchingMessage, $additionalParameters = [])
    {
        $parameters = parent::buildServicePayload($message, $matchingMessage, $additionalParameters);

        if (strlen(utf8_decode($parameters['text'])) !== strlen($parameters['text'])) {
            $parameters['type'] = 'unicode';
        }

        return $parameters;
    }
}
