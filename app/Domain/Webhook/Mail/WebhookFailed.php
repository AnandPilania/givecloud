<?php

namespace Ds\Domain\Webhook\Mail;

use Ds\Models\HookDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WebhookFailed extends Mailable
{
    use SerializesModels;
    use Queueable;

    /** @var \Ds\Models\HookDelivery */
    protected $delivery;

    /** @var string */
    protected $error;

    /**
     * Create a new message instance.
     *
     * @param \Ds\Models\HookDelivery $delivery
     * @param string $error
     * @return void
     */
    public function __construct(HookDelivery $delivery, string $error)
    {
        $this->delivery = $delivery;
        $this->error = $error;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->view('mailables.webhook-failed', [
                'hook_delivery' => $this->delivery,
                'error_message' => $this->error,
            ])->subject('Webhook Failure');
    }
}
