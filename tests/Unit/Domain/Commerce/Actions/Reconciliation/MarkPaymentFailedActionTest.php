<?php

namespace Tests\Unit\Domain\Commerce\Actions\Reconciliation;

use Ds\Domain\Commerce\Actions\Reconciliation\MarkPaymentFailedAction;
use Ds\Enums\PaymentStatus;
use Tests\StoryBuilder;
use Tests\TestCase;

class MarkPaymentFailedActionTest extends TestCase
{
    public function testPaymentAndTransactionMarkedAsFailed(): void
    {
        $rpp = StoryBuilder::recurringContribution()
            ->usingBankAccount()
            ->includingPayments(1)
            ->create();

        $payment = $rpp->last_transaction->payments[0];
        $transaction = $rpp->last_transaction;

        $this->app[MarkPaymentFailedAction::class]->execute($payment);

        $this->assertSame($payment->refresh()->status, PaymentStatus::FAILED);
        $this->assertSame($transaction->refresh()->payment_status, 'Denied');
    }
}
