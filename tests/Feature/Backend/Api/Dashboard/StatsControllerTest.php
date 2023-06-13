<?php

namespace Tests\Feature\Backend\Api\Dashboard;

use Carbon\Carbon;
use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Domain\Commerce\Responses\TransactionResponse;
use Ds\Models\Member;
use Ds\Models\Order;
use Ds\Models\OrderItem;
use Ds\Models\RecurringPaymentProfile;
use Ds\Models\Transaction;
use Ds\Services\OrderService;
use Ds\Services\StatsService;
use Ds\Services\TransactionService;
use Tests\TestCase;

/** @group dashboard */
class StatsControllerTest extends TestCase
{
    public function testUnauthorizedReturnsJsonError(): void
    {
        $this->actingAsUser()
            ->get(route('dashboard.stats'))
            ->assertJsonPath('error', 'You are not authorized to perform this action.');
    }

    public function testInvokeReturnsStats(): void
    {
        Carbon::setTestNow(fromLocal('2022-09-19 13:47:02'));

        $member = Member::factory()->create([
            'first_payment_at' => toUtc(fromLocal('now')),
        ]);

        $otherMember = Member::factory(4)->create([
            'first_payment_at' => toUtc(fromLocal('now'))->subMonthsWithoutOverflow(),
        ])->first();

        $orderLastMonth = Order::factory()->paid()->completed()->create([
            'member_id' => $otherMember->getKey(),
            'ordered_at' => toUtc(fromLocal('now'))->subMonthsWithoutOverflow(),
        ]);

        $order = Order::factory()->paid()->completed()->create(['member_id' => $member->getKey()]);
        $anotherOrder = Order::factory()->paid()->completed()->create(['member_id' => $member->getKey()]);

        $rpp = RecurringPaymentProfile::factory()->create([
            'member_id' => $member,
            'productorder_id' => $order,
            'productorderitem_id' => OrderItem::factory()->state(['productorderid' => $order]),
        ]);

        $transaction = Transaction::factory()->paid()->create([
            'recurring_payment_profile_id' => $rpp,
            'order_time' => toUtc(fromLocal('yesterday')),
        ]);

        $transactionLastMonth = Transaction::factory()->paid()->create([
            'recurring_payment_profile_id' => $rpp,
            'order_time' => toUtc(fromLocal('now'))->subMonthsWithoutOverflow(),
        ]);

        $orderRevenues = collect([$order, $anotherOrder])->sum('functional_total');
        $revenues = collect([$order, $anotherOrder, $transaction])->sum('functional_total');
        $revenuesLastMonth = collect([$orderLastMonth, $transactionLastMonth])->sum('functional_total');

        $res = new TransactionResponse(PaymentProvider::getCreditCardProvider(), [
            'completed' => true,
            'response' => '1',
            'response_text' => 'AP',
            'cc_number' => '1111',
            'cc_exp' => '1234',
        ]);

        app(OrderService::class)->createPaymentFromTransactionResponse($order, $res);
        app(OrderService::class)->createPaymentFromTransactionResponse($anotherOrder, $res);
        app(OrderService::class)->createPaymentFromTransactionResponse($orderLastMonth, $res);

        app(TransactionService::class)->createPayment($transaction, $res);
        app(TransactionService::class)->createPayment($transactionLastMonth, $res);

        $childrenStructure = [
            'period' => [
                'value',
                'formatted',
            ],
            'previous' => [
                'value',
                'formatted',
            ],
            'diff',
            'increasing',
        ];

        $this->actingAsAdminUser()
            ->get(route('dashboard.stats'))
            ->assertJsonStructure([
                'totals' => [
                    'period',
                    'previous',
                    'diff',
                    'increasing',
                ],
                'contributions' => $childrenStructure,
                'daily_revenue' => $childrenStructure,
                'one_time' => $childrenStructure,
                'recurring' => $childrenStructure,
                'supporters' => $childrenStructure,
            ])->assertJsonPath('totals.period', money($revenues)->format('$0[.]0A'))
            ->assertJsonPath('totals.previous', money($revenuesLastMonth)->format('$0[.]0A'))
            ->assertJsonPath('totals.diff', $this->app->make(StatsService::class)->difference($revenues, $revenuesLastMonth))
            ->assertJsonPath('totals.increasing', $revenues > $revenuesLastMonth)
            ->assertJsonPath('one_time.period.formatted', money($orderRevenues)->format('$0[.]0A'))
            ->assertJsonPath('one_time.previous.formatted', money($orderLastMonth->functional_total)->format('$0[.]0A'))
            ->assertJsonPath('one_time.diff', $this->app->make(StatsService::class)->difference($orderRevenues, $orderLastMonth->functional_total))
            ->assertJsonPath('one_time.increasing', $orderRevenues > $orderLastMonth->functional_total)
            ->assertJsonPath('recurring.period.formatted', money($transaction->functional_total)->format('$0[.]0A'))
            ->assertJsonPath('recurring.previous.formatted', money($transactionLastMonth->functional_total)->format('$0[.]0A'))
            ->assertJsonPath('recurring.diff', $this->app->make(StatsService::class)->difference($transaction->functional_total, $transactionLastMonth->functional_total))
            ->assertJsonPath('recurring.increasing', $transaction->functional_total > $transactionLastMonth->functional_total)
            ->assertJsonPath('supporters.period.formatted', '1')
            ->assertJsonPath('supporters.previous.formatted', '4')
            ->assertJsonPath('supporters.diff', -75)
            ->assertJsonPath('supporters.increasing', false);

        Carbon::setTestNow();
    }
}
