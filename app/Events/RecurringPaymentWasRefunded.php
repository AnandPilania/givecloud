<?php

namespace Ds\Events;

use Ds\Models\RecurringPaymentProfile;
use Ds\Models\Transaction;
use Illuminate\Queue\SerializesModels;

class RecurringPaymentWasRefunded extends Event implements RecurringPaymentEventInterface
{
    use SerializesModels;

    public RecurringPaymentProfile $rpp;
    public Transaction $transaction;

    public function __construct(RecurringPaymentProfile $rpp, Transaction $transaction)
    {
        $this->rpp = $rpp;
        $this->transaction = $transaction;
    }
}
