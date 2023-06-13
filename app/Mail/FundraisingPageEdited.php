<?php

namespace Ds\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FundraisingPageEdited extends Mailable
{
    use SerializesModels;
    use Queueable;

    /** @var array */
    protected $mergeData;

    /**
     * Create a new message instance.
     *
     * @param array $mergeData
     * @return void
     */
    public function __construct(array $mergeData)
    {
        $this->mergeData = $mergeData;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->view('mailables.fundraising-page-edited', $this->mergeData)
            ->subject("Edited Fundraiser: '" . $this->mergeData['page_name'] . "'");
    }
}
