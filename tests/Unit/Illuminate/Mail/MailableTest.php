<?php

namespace Tests\Unit\Illuminate\Mail;

use Ds\Illuminate\Mail\Mailable;
use Tests\TestCase;

class MailableTest extends TestCase
{
    public function testMailableDefaultState()
    {
        $mailable = new Mailable;

        $this->assertFalse($mailable->fromSubscriber);
        $this->assertEmpty($mailable->from);
        $this->assertEmpty($mailable->replyTo);
        $this->assertEmpty($mailable->callbacks);
    }

    public function testMailableFromSubscriber()
    {
        sys_set('email_replyto_address', false);
        sys_set('email_sender_required', false);

        $mailable = (new Mailable)->fromSubscriber();

        $this->assertTrue($mailable->fromSubscriber);
        $this->assertNotEmpty($mailable->from);
        $this->assertEmpty($mailable->replyTo);
        $this->assertEmpty($mailable->callbacks);
    }

    public function testMailableFromSubscriberWithReplyTo()
    {
        sys_set('email_replyto_address', true);
        sys_set('email_sender_required', false);

        $mailable = (new Mailable)->fromSubscriber();

        $this->assertTrue($mailable->fromSubscriber);
        $this->assertNotEmpty($mailable->from);
        $this->assertNotEmpty($mailable->replyTo);
        $this->assertEmpty($mailable->callbacks);
    }

    public function testMailableFromSubscriberWithReplyToAndSenderRequired()
    {
        sys_set('email_replyto_address', true);
        sys_set('email_sender_required', true);

        $mailable = (new Mailable)->fromSubscriber();

        $this->assertTrue($mailable->fromSubscriber);
        $this->assertNotEmpty($mailable->from);
        $this->assertNotEmpty($mailable->replyTo);
        $this->assertNotEmpty($mailable->callbacks);
    }
}
