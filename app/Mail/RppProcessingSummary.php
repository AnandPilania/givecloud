<?php

namespace Ds\Mail;

use Ds\Models\Email;
use Ds\Models\RecurringBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RppProcessingSummary extends Mailable
{
    use SerializesModels;
    use Queueable;

    /** @var \Ds\Models\RecurringBatch */
    protected $recurringBatch;

    public function __construct(RecurringBatch $recurringBatch)
    {
        $this->recurringBatch = $recurringBatch;
    }

    public function build()
    {
        $data = [
            'is_auto_follow_up' => Email::activeType('customer_recurring_payment_failure')->isNotEmpty(),
            'transactions_approved' => $this->recurringBatch->transactions_approved,
            'transactions_declined' => $this->recurringBatch->transactions_declined,
            'transactions_processed' => $this->recurringBatch->transactions_approved + $this->recurringBatch->transactions_declined,
            'transactions_url' => secure_site_url(sprintf(
                '/jpanel/reports/transactions?ordertime_str=%s&ordertime_end=%s',
                fromLocalFormat($this->recurringBatch->batched_on, 'Y-m-d'),
                fromLocalFormat($this->recurringBatch->batched_on, 'Y-m-d'),
            )),
        ];

        return $this
            ->view('mailables.rpp-processing-summary', $data)
            ->subject(sprintf(
                '%s Recurring Payment Summary: (%s) Profiles',
                fromLocalFormat($this->recurringBatch->batched_on, 'M j, Y'),
                $data['transactions_processed']
            ));
    }
}
