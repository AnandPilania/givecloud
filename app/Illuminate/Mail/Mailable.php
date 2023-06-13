<?php

namespace Ds\Illuminate\Mail;

use Illuminate\Mail\Mailable as IlluminateMailable;

class Mailable extends IlluminateMailable
{
    public bool $fromSubscriber = false;

    public function fromSubscriber(bool $fromSubscriber = true): self
    {
        $this->fromSubscriber = $fromSubscriber;

        if ($fromSubscriber) {
            $this->from(
                sys_get('email_from_address'),
                sys_get('email_from_name', sys_get('clientShortName')),
            );

            if (sys_get('email_replyto_address')) {
                $this->replyTo(sys_get('email_replyto_address'));
            }

            if (sys_get('email_sender_required')) {
                $this->withSwiftMessage(fn ($message) => $message->setSender('notifications@givecloud.co'));
            }
        }

        return $this;
    }
}
