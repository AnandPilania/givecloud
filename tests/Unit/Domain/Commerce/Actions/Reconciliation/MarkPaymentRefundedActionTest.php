<?php

namespace Tests\Unit\Domain\Commerce\Actions\Reconciliation;

use Ds\Domain\Commerce\Actions\Reconciliation\MarkPaymentRefundedAction;
use Ds\Enums\PaymentStatus;
use Tests\StoryBuilder;
use Tests\TestCase;

class MarkPaymentRefundedActionTest extends TestCase
{
    public function testPaymentAndTransactionMarkedAsRefunded(): void
    {
        $rpp = StoryBuilder::recurringContribution()
            ->usingBankAccount()
            ->includingPayments(1)
            ->create();

        $payment = $rpp->last_transaction->payments[0];
        $transaction = $rpp->last_transaction;

        $this->app[MarkPaymentRefundedAction::class]->execute($payment, 're_942a79b59d0cd5bd', now());

        $payment->refresh();
        $transaction->refresh();

        $this->assertSame($payment->status, PaymentStatus::SUCCEEDED);
        $this->assertTrue($payment->refunded);

        $this->assertSame($transaction->payment_status, 'Completed');
        $this->assertNotNull($transaction->refunded_at);
    }
}
