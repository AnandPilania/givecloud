<?php

namespace Tests\Unit\Domain\Commerce\Actions\Reconciliation;

use Ds\Domain\Commerce\Actions\Reconciliation\MarkPaymentSucceededAction;
use Ds\Enums\PaymentStatus;
use Tests\StoryBuilder;
use Tests\TestCase;

class MarkPaymentSucceededActionTest extends TestCase
{
    public function testPaymentAndTransactionMarkedAsSucceeded(): void
    {
        $rpp = StoryBuilder::recurringContribution()
            ->usingBankAccount()
            ->includingPayments(1)
            ->create();

        $payment = $rpp->last_transaction->payments[0];
        $transaction = $rpp->last_transaction;

        $this->app[MarkPaymentSucceededAction::class]->execute($payment);

        $this->assertSame($payment->refresh()->status, PaymentStatus::SUCCEEDED);
        $this->assertSame($transaction->refresh()->payment_status, 'Completed');
    }
}
