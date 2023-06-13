<?php

namespace Tests\Unit\Listeners\Supporter;

use Ds\Events\OrderWasCompleted;
use Ds\Events\RecurringPaymentWasCompleted;
use Ds\Listeners\Supporter\UpdateLastPaymentDate;
use Ds\Models\Account;
use Ds\Models\Order;
use Ds\Models\Payment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class UpdateLastPaymentDateTest extends TestCase
{
    public function testEventListenerIsListeningOnOrderCreated(): void
    {
        Event::fake();

        Event::assertListening(OrderWasCompleted::class, UpdateLastPaymentDate::class);
    }

    public function testEventListenerIsListeningOnRecurringPaymentWasCompleted(): void
    {
        Event::fake();

        Event::assertListening(RecurringPaymentWasCompleted::class, UpdateLastPaymentDate::class);
    }

    public function testJobsUpdatesSupportersFirstAndLastPaymentDate(): void
    {
        $time = Carbon::parse('2021-04-19 02:22:22');
        $this->travelTo($time);

        $account = Account::factory()->create();
        $order = Order::factory()->create(['member_id' => $account]);
        Payment::factory()->by($account)->paid()->create();

        (new UpdateLastPaymentDate)->handle((new OrderWasCompleted($order)));

        $account->refresh();

        $this->assertSame($time->toDateTimeString(), $account->first_payment_at);
        $this->assertSame($time->toDateTimeString(), $account->last_payment_at);
    }

    public function testJobsUpdatesSupporterLastPaymentDate(): void
    {
        $firstOrderTime = Carbon::parse('2021-04-19 02:22:22');
        $secondOrderTime = Carbon::parse('2021-04-19 04:44:44');

        $account = Account::factory()->create();
        $order = Order::factory()->create(['member_id' => $account]);

        $this->travelTo($firstOrderTime);
        Payment::factory()->paid()->by($account)->create();

        $this->travelTo($secondOrderTime);
        Payment::factory()->paid()->by($account)->create();

        (new UpdateLastPaymentDate)->handle((new OrderWasCompleted($order)));

        $account->refresh();

        $this->assertSame($firstOrderTime->toDateTimeString(), $account->first_payment_at);
        $this->assertSame($secondOrderTime->toDateTimeString(), $account->last_payment_at);
    }

    public function testLastPaymentAlsoReflectsRefundedPayments(): void
    {
        $account = Account::factory()->create();
        $order = Order::factory()->create(['member_id' => $account]);

        Payment::factory()->paid()->by($account)->create();

        $this->travelTo(Carbon::today()->addDay());

        Payment::factory()->paid()->by($account)->refunded()->create();

        (new UpdateLastPaymentDate)->handle((new OrderWasCompleted($order)));

        $account->refresh();

        $this->travelBack();

        $this->assertSame(Carbon::today()->addDay()->toDateTimeString(), $account->last_payment_at);
    }
}
