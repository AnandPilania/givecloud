<?php

namespace Ds\Domain\Commerce\Actions\Reconciliation;

use DateTimeInterface;
use Ds\Enums\PaymentStatus;
use Ds\Events\OrderWasRefunded;
use Ds\Events\RecurringPaymentWasRefunded;
use Ds\Models\Payment;
use Ds\Models\Refund;

class MarkPaymentRefundedAction
{
    public function execute(Payment $payment, string $referenceNumber, DateTimeInterface $refundedAt): void
    {
        if ($payment->amount_refunded) {
            return;
        }

        $refund = $this->updatePaymentAndCreateRefund($payment, $referenceNumber, $refundedAt);

        $this->updateLinkedContributions($payment, $refund);
        $this->updateLinkedTransactions($payment, $refund);
    }

    private function updatePaymentAndCreateRefund(Payment $payment, string $referenceNumber, DateTimeInterface $refundedAt): Refund
    {
        $payment->status = PaymentStatus::SUCCEEDED;
        $payment->paid = true;
        $payment->save();

        $refund = new Refund;
        $refund->status = 'succeeded';
        $refund->reference_number = $referenceNumber;
        $refund->amount = $payment->amount;
        $refund->currency = $payment->currency;
        $refund->reason = 'requested_by_customer';
        $refund->refunded_by_id = 1;
        $refund->created_at = $refundedAt;

        $payment->refunds()->save($refund);

        return $refund;
    }

    private function updateLinkedContributions(Payment $payment, Refund $refund): void
    {
        foreach ($payment->orders as $contribution) {
            $contribution->refunded_auth = $refund->reference_number;
            $contribution->refunded_at = fromUtc($refund->created_at);
            $contribution->refunded_amt = $refund->amount;
            $contribution->refunded_by = $refund->refunded_by_id;
            $contribution->save();

            event(new OrderWasRefunded($contribution));
        }
    }

    private function updateLinkedTransactions(Payment $payment, Refund $refund): void
    {
        foreach ($payment->transactions as $transaction) {
            $transaction->refunded_auth = $refund->reference_number;
            $transaction->refunded_at = fromUtc($refund->created_at);
            $transaction->refunded_amt = $refund->amount;
            $transaction->refunded_by = $refund->refunded_by_id;
            $transaction->save();

            $transaction->recurringPaymentProfile->refreshAggregateAmount();

            event(new RecurringPaymentWasRefunded($transaction->recurringPaymentProfile, $transaction));
        }
    }
}
