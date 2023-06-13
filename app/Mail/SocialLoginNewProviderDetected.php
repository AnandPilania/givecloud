<?php

namespace Ds\Mail;

use Ds\Illuminate\Mail\Mailable;
use Ds\Models\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class SocialLoginNewProviderDetected extends Mailable
{
    use Queueable;
    use SerializesModels;

    public string $provider;
    public Member $member;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Member $member, string $provider)
    {
        $this->member = $member;
        $this->provider = $provider;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mailables.social-login-new-provider-detected')->fromSubscriber();
    }
}
