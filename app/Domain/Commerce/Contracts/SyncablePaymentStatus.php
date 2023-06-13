<?php

namespace Ds\Domain\Commerce\Contracts;

use Ds\Models\Payment;

interface SyncablePaymentStatus
{
    /**
     * Sync payment status.
     *
     * @param \Ds\Models\Payment $payment
     * @return void
     */
    public function syncPaymentStatus(Payment $payment): void;
}
