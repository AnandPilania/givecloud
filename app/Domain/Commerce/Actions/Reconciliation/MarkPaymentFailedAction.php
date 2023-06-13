<?php

namespace Ds\Domain\Commerce\Actions\Reconciliation;

use Ds\Enums\PaymentStatus;
use Ds\Models\Payment;
use Ds\Services\LedgerEntryService;

class MarkPaymentFailedAction
{
    private LedgerEntryService $ledgerEntryService;

    public function __construct(LedgerEntryService $ledgerEntryService)
    {
        $this->ledgerEntryService = $ledgerEntryService;
    }

    public function execute(Payment $payment): void
    {
        if ($payment->status === PaymentStatus::FAILED) {
            return;
        }

        $this->updatePayment($payment);

        $this->updateLinkedContributions($payment);
        $this->updateLinkedTransactions($payment);
    }

    private function updatePayment(Payment $payment): void
    {
        $payment->status = PaymentStatus::FAILED;
        $payment->paid = false;
        $payment->captured = false;
        $payment->captured_at = null;
        $payment->save();
    }

    private function updateLinkedContributions(Payment $payment): void
    {
        foreach ($payment->orders as $contribution) {
            $contribution->is_processed = false; // used to track "paid" contributions in many legacy queries
            $contribution->confirmationdatetime = null; // used to track "paid" contributions for most existing queries and scopes
            $contribution->invoicenumber = null;
            $contribution->save();

            $this->ledgerEntryService->make($contribution);
        }
    }

    private function updateLinkedTransactions(Payment $payment): void
    {
        foreach ($payment->transactions as $transaction) {
            $transaction->setAttribute('transaction_status', 'Error');
            $transaction->setAttribute('payment_status', 'Denied');
            $transaction->save();

            $this->ledgerEntryService->make($transaction);
        }
    }
}
