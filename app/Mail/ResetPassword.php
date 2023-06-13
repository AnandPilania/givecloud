<?php

namespace Ds\Mail;

use Ds\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPassword extends Mailable
{
    use SerializesModels;
    use Queueable;

    /** @var \Ds\Models\User */
    protected $user;

    /** @var string */
    protected $token;

    /**
     * Create a new message instance.
     *
     * @param \Ds\Models\User $user
     * @param string $token
     * @return void
     */
    public function __construct(User $user, string $token)
    {
        $this->user = $user;
        $this->token = $token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->view('mailables.reset-password', [
                'user' => $this->user,
                'token' => $this->token,
            ])->subject('Your Password Reset Link');
    }
}
