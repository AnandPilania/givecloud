<?php

namespace Ds\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FundraisingPageActivated extends Mailable
{
    use SerializesModels;
    use Queueable;

    /** @var array */
    protected $merge_data;

    /**
     * Create a new message instance.
     *
     * @param array $merge_data
     * @return void
     */
    public function __construct(array $merge_data)
    {
        $this->merge_data = $merge_data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->view('mailables.fundraising-page-activated', $this->merge_data)
            ->subject("New Fundraiser: '" . $this->merge_data['page_name'] . "'");
    }
}
