<?php

namespace Ds\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubmitToEmail extends Mailable
{
    use SerializesModels;
    use Queueable;

    /** @var array */
    protected $fields;

    /**
     * Create a new message instance.
     *
     * @param string $subject
     * @param array $fields
     * @return void
     */
    public function __construct(string $subject, array $fields)
    {
        $this->subject = $subject;
        $this->fields = $fields;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mailables.submit-to-email', [
            'subject' => $this->subject,
            'fields' => $this->fields,
        ]);
    }
}
