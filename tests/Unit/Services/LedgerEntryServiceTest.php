<?php

namespace Tests\Unit\Services;

use Ds\Enums\LedgerEntryType;
use Ds\Models\Order;
use Ds\Models\Transaction;
use Ds\Models\User;
use Ds\Services\LedgerEntryService;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Tests\Concerns\InteractsWithRpps;
use Tests\TestCase;

class LedgerEntryServiceTest extends TestCase
{
    use WithFaker;
    use InteractsWithRpps;

    public function testReturnsFalseForNonLedgerableModel(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->app->make(LedgerEntryService::class)->make(User::factory()->create());
    }

    public function testServiceCanAddDCCLineFromOrder(): void
    {
        $recurringDccAmount = $this->faker->randomFloat(2, 1);

        $order = Order::factory()->dcc()->paid()->create();
        $transaction = $this->createTransactionWithRPP();
        $transaction->dcc_amount = $recurringDccAmount;

        $this->app->make(LedgerEntryService::class)->make($order);
        $transactionEntries = $this->app->make(LedgerEntryService::class)->make($transaction);

        $this->assertSame($recurringDccAmount, $transactionEntries->firstWhere('type', LedgerEntryType::DCC)->amount);

        // DCC is on the LineItem Level.
        $this->assertDatabaseMissing('ledger_entries', [
            'ledgerable_type' => 'order',
            'type' => LedgerEntryType::DCC,
        ]);

        $this->assertDatabaseHas('ledger_entries', [
            'ledgerable_type' => Transaction::class,
            'type' => LedgerEntryType::DCC,
            'amount' => $recurringDccAmount,
            'order_id' => $transaction->recurringPaymentProfile->productorder_id,
            'item_id' => $transaction->recurringPaymentProfile->productorderitem_id,
        ]);
    }

    public function testServiceCanAddShippingLineFromOrder(): void
    {
        $order = Order::factory()->paid()->shipped()->create();
        $transaction = $this->createTransactionWithRPP();

        $orderEntries = $this->app->make(LedgerEntryService::class)->make($order);
        $transactionEntries = $this->app->make(LedgerEntryService::class)->make($transaction);

        $this->assertSame($orderEntries->firstWhere('type', LedgerEntryType::SHIPPING)->amount, $order->shipping_amount);
        $this->assertSame($transactionEntries->firstWhere('type', LedgerEntryType::SHIPPING)->amount, $transaction->shipping_amt);

        $this->assertDatabaseHas('ledger_entries', [
            'ledgerable_type' => 'order',
            'type' => LedgerEntryType::SHIPPING,
            'amount' => $order->shipping_amount,
            'order_id' => $order->id,
        ]);

        $this->assertDatabaseHas('ledger_entries', [
            'ledgerable_type' => Transaction::class,
            'type' => LedgerEntryType::SHIPPING,
            'amount' => $transaction->shipping_amt,
            'order_id' => $transaction->recurringPaymentProfile->productorder_id,
        ]);
    }

    public function testServiceCanAddTaxLineFromModels(): void
    {
        $order = Order::factory()->paid()->taxed()->create();
        $transaction = $this->createTransactionWithRPP();

        $orderEntries = $this->app->make(LedgerEntryService::class)->make($order);
        $transactionEntries = $this->app->make(LedgerEntryService::class)->make($transaction);

        $this->assertSame($orderEntries->firstWhere('type', LedgerEntryType::TAX)->amount, $order->taxtotal);
        $this->assertSame($transactionEntries->firstWhere('type', LedgerEntryType::TAX)->amount, $transaction->tax_amt);

        $this->assertDatabaseHas('ledger_entries', [
            'type' => LedgerEntryType::TAX,
            'amount' => $order->taxtotal,
            'order_id' => $order->id,
        ]);

        $this->assertDatabaseHas('ledger_entries', [
            'type' => LedgerEntryType::TAX,
            'amount' => $transaction->tax_amt,
            'order_id' => $transaction->recurringPaymentProfile->productorder_id,
        ]);
    }

    public function testServiceCanAddLineItemsFromModels(): void
    {
        $order = Order::factory()->paid()->hasItems()->create()->load('items');
        $transaction = $this->createTransactionWithRPP();

        $orderEntries = $this->app->make(LedgerEntryService::class)->make($order);
        $transactionEntries = $this->app->make(LedgerEntryService::class)->make($transaction);

        $this->assertSame(
            $orderEntries->firstWhere('type', LedgerEntryType::LINE_ITEM)->amount,
            $order->items()->first()->price
        );

        $this->assertSame(
            $transactionEntries->firstWhere('type', LedgerEntryType::LINE_ITEM)->amount,
            $transaction->subtotal_amount
        );

        $this->assertDatabaseHas('ledger_entries', [
            'type' => LedgerEntryType::LINE_ITEM,
            'amount' => $order->items()->first()->price,
            'order_id' => $order->id,
            'item_id' => $order->items()->first()->id,
        ]);

        $this->assertDatabaseHas('ledger_entries', [
            'type' => LedgerEntryType::LINE_ITEM,
            'amount' => $transaction->subtotal_amount,
            'order_id' => $transaction->recurringPaymentProfile->productorder_id,
        ]);
    }

    public function testServiceUsesCapturedAtDateForOrders(): void
    {
        $confirmedDate = Carbon::parse('2021-10-29 02:22:22');
        $posOverride = Carbon::parse('2021-09-29 02:22:22');

        $order = Order::factory()->paid()->hasItems()->create([
            'confirmationdatetime' => $confirmedDate,
            'ordered_at' => $posOverride,
        ])->load('items');

        $this->app->make(LedgerEntryService::class)->make($order);

        $this->assertDatabaseHas('ledger_entries', [
            'type' => LedgerEntryType::LINE_ITEM,
            'amount' => $order->items()->first()->price,
            'order_id' => $order->id,
            'item_id' => $order->items()->first()->id,
            'captured_at' => $posOverride,
        ]);
    }
}
