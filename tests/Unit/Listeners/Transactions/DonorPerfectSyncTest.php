<?php

namespace Tests\Unit\Listeners\Transactions;

use Ds\Events\RecurringPaymentWasCompleted;
use Ds\Listeners\Transactions\DonorPerfectSync;
use Ds\Models\Account;
use Ds\Models\PaymentMethod;
use Ds\Models\Transaction;
use Ds\Services\DonorPerfectService;
use Illuminate\Support\Facades\Event;
use Tests\Concerns\InteractsWithRpps;
use Tests\TestCase;

class DonorPerfectSyncTest extends TestCase
{
    use InteractsWithRpps;

    public function testListenerListensForEvent(): void
    {
        Event::fake();

        Event::assertListening(RecurringPaymentWasCompleted::class, DonorPerfectSync::class);
    }

    public function testListenerDoesNotCallServiceWhenDPOIsNotEnabled(): void
    {
        $this->assertFalse(app(DonorPerfectSync::class)->shouldQueue($this->recurringPaymentWasCompletedEvent()));
    }

    public function testListenerDoesNotCallServiceWhenAutoSyncIsNotEnabled(): void
    {
        $event = $this->recurringPaymentWasCompletedEvent();
        $event->transaction->dp_auto_sync = false;
        $this->assertFalse(app(DonorPerfectSync::class)->shouldQueue($event));
    }

    public function testListenerQueuesWhenDPOIsEnabled(): void
    {
        sys_set('dpo_api_key', 'some_api_key');
        $this->assertTrue(app(DonorPerfectSync::class)->shouldQueue($this->recurringPaymentWasCompletedEvent()));
    }

    public function testListenerCallsCommitToDpo(): void
    {
        sys_set('dpo_api_key', 'some_api_key');

        $this->mock(DonorPerfectService::class)->shouldReceive('pushTransaction')->once()->andReturnTrue();

        app(DonorPerfectSync::class)->handle($this->recurringPaymentWasCompletedEvent());
    }

    private function recurringPaymentWasCompletedEvent(): RecurringPaymentWasCompleted
    {
        return new RecurringPaymentWasCompleted(
            $this->generateRpp(Account::factory()->create(), PaymentMethod::factory()->create()),
            Transaction::factory()->create(['payment_status' => 'Completed'])
        );
    }
}
