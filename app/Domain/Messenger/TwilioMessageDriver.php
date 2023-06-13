<?php

namespace Ds\Domain\Messenger;

use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use BotMan\Drivers\Twilio\TwilioMessageDriver as BotManTwilioMessageDriver;
use Symfony\Component\HttpFoundation\Response;
use Twilio\Rest\Client as Twilio;
use Twilio\TwiML\MessagingResponse;

class TwilioMessageDriver extends BotManTwilioMessageDriver
{
    /** @var OutgoingMessage[] */
    protected $replies = [];

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
     * Set the From number.
     *
     * @param string $fromNumber
     */
    public function setFromNumber($fromNumber)
    {
        $this->config->put('fromNumber', $fromNumber);
    }

    /**
     * Send a payload (or queue for deferred sending).
     *
     * @param mixed $payload
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Twilio\Rest\Api\V2010\Account\TwilioException
     */
    public function sendPayload($payload)
    {
        if (isset($payload['twiml'])) {
            return Response::create((string) $payload['twiml'])->send();
        }

        if (isset($payload['originate']) && $payload['originate'] === true) {
            if (! $this->client) {
                $this->client = new Twilio($this->config->get('sid'), $this->config->get('token'));
            }

            $originatePayload = [
                'from' => $this->config->get('fromNumber'),
                'body' => $payload['text'],
            ];

            if (isset($payload['media'])) {
                $originatePayload['mediaUrl'] = $payload['media'];
            }

            $message = $this->client->messages->create($payload['recipient'], $originatePayload);

            return Response::create(json_encode($message->toArray()));
        }

        $this->replies[] = $payload;
    }

    /**
     * Build a reply contains all the messages.
     *
     * @param array $replies
     * @return \Twilio\TwiML\MessagingResponse
     */
    protected function buildReply(array $replies)
    {
        $response = new MessagingResponse;

        foreach ($replies as $reply) {
            $message = $response->message('');
            $body = $reply['text'];

            foreach ((array) $reply['buttons'] as $button) {
                $body .= "\n" . $reply['text'];
            }

            $message->body($body);
            if (isset($reply['media'])) {
                $message->media($reply['media']);
            }
        }

        return $response;
    }

    /**
     * Send out message response.
     */
    public function messagesHandled()
    {
        $response = $this->buildReply($this->replies);

        // Reset replies
        $this->replies = [];

        Response::create((string) $response)->send();
    }
}
