<?php

namespace Ds\Domain\Commerce\Actions\Reconciliation;

use Ds\Enums\PaymentStatus;
use Ds\Models\Payment;
use Ds\Services\LedgerEntryService;

class MarkPaymentSucceededAction
{
    private LedgerEntryService $ledgerEntryService;

    public function __construct(LedgerEntryService $ledgerEntryService)
    {
        $this->ledgerEntryService = $ledgerEntryService;
    }

    public function execute(Payment $payment): void
    {
        if ($payment->status === PaymentStatus::SUCCEEDED) {
            return;
        }

        $this->updatePayment($payment);

        $this->updateLinkedContributions($payment);
        $this->updateLinkedTransactions($payment);
    }

    private function updatePayment(Payment $payment): void
    {
        $payment->status = PaymentStatus::SUCCEEDED;
        $payment->paid = true;
        $payment->save();
    }

    private function updateLinkedContributions(Payment $payment): void
    {
        foreach ($payment->orders as $contribution) {
            $contribution->is_processed = true; // used to track "paid" contributions in many legacy queries
            $contribution->confirmationdatetime ??= $contribution->createddatetime; // used to track "paid" contributions for most existing queries and scopes
            $contribution->invoicenumber = $contribution->client_uuid;
            $contribution->save();

            $this->ledgerEntryService->make($contribution);
        }
    }

    private function updateLinkedTransactions(Payment $payment): void
    {
        foreach ($payment->transactions as $transaction) {
            $transaction->setAttribute('transaction_status', 'Completed');
            $transaction->setAttribute('payment_status', 'Completed');
            $transaction->save();

            $this->ledgerEntryService->make($transaction);
        }
    }
}
