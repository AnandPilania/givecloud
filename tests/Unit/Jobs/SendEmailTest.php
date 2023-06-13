<?php

namespace Tests\Unit\Jobs;

use Ds\Jobs\SendEmail;
use Swift_Message;
use Tests\TestCase;

class SendEmailTest extends TestCase
{
    public function testSenderIncludedWhenRequired()
    {
        sys_set(['email_sender_required' => true]);

        $message = $this->getSwiftMessage();

        dispatch(new SendEmail($message));

        $this->assertArrayHasKey('notifications@givecloud.co', $message->getSender());
    }

    public function testSenderNotIncludedWhenNotRequired()
    {
        sys_set(['email_sender_required' => false]);

        $message = $this->getSwiftMessage();

        dispatch(new SendEmail($message));

        $this->assertNull($message->getSender());
    }

    private function getSwiftMessage(): Swift_Message
    {
        return (new Swift_Message)
            ->addTo('randy@example.com')
            ->setSubject('Suspendisse tempor diam enim')
            ->setBody('Pellentesque et purus ac erat. Curabitur convallis dictum egestas. Aenean.');
    }
}
