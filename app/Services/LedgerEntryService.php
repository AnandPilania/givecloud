<?php

namespace Ds\Services;

use BadMethodCallException;
use Ds\Domain\Shared\DateTime;
use Ds\Enums\LedgerEntryType;
use Ds\Illuminate\Database\Eloquent\Model;
use Ds\Models\LedgerEntry;
use Ds\Models\Order;
use Ds\Models\OrderItem;
use Ds\Models\Traits\HasLedgerEntries;
use Ds\Models\Transaction;
use Illuminate\Support\Collection;

class LedgerEntryService
{
    /**
     * @throws \BadMethodCallException
     */
    public function make(Model $model): Collection
    {
        if (! $this->modelIsLedgerable($model)) {
            throw new BadMethodCallException;
        }

        $this->clearExistingEntries($model);

        if (! $model->is_refundable || ! $this->isPaid($model)) {
            return collect([]);
        }

        $entries = collect()
            ->merge($this->createLineItems($model))
            ->merge($this->createShippingLines($model))
            ->merge($this->createDCCLines($model))
            ->merge($this->createTaxLines($model));

        return $this->saveEntries($model, $entries);
    }

    protected function clearExistingEntries(Model $model): bool
    {
        return $model->ledgerEntries()->delete();
    }

    protected function createLineItems(Model $model): array
    {
        if (is_a($model, Order::class)) {
            return $this->createOrderLineItems($model);
        }

        if (is_a($model, Transaction::class)) {
            return $this->createTransactionLineItems($model);
        }

        return [];
    }

    protected function createTransactionLineItems(Transaction $transaction): array
    {
        if (! $transaction->recurringPaymentProfile) {
            return [];
        }

        $rpp = $transaction->recurringPaymentProfile;

        if (! $item = $rpp->order_item) {
            return [];
        }

        return [
            new LedgerEntry([
                'type' => LedgerEntryType::LINE_ITEM,
                'captured_at' => $this->getCapturedAt($transaction),

                'amount' => $transaction->subtotal_amount ?: 0,
                'discount' => 0,
                'gl_account' => $rpp->gl_code,
                'qty' => 1,
                'original_amount' => $transaction->subtotal_amount ?: 0,

                'fundraising_page_id' => $item->fundraising_page_id,
                'item_id' => $item->getKey(),
                'sponsorship_id' => $rpp->sponsorship_id,
                'supporter_id' => $rpp->member_id,
            ]),
        ];
    }

    protected function createOrderLineItems(Order $model): array
    {
        return $model->items->map(function (OrderItem $item) use ($model) {
            return new LedgerEntry([
                'type' => LedgerEntryType::LINE_ITEM,
                'captured_at' => $this->getCapturedAt($model),

                'amount' => $item->price ?: 0,
                'discount' => $item->discount ?: 0,
                'gl_account' => $item->gl_code,
                'qty' => $item->qty ?: 1,
                'original_amount' => $item->orignal_price ?: 0,

                'fundraising_page_id' => $item->fundraising_page_id,
                'item_id' => $item->getKey(),
                'sponsorship_id' => $item->sponsorship_id,
                'supporter_id' => $item->order->member_id,
            ]);
        })->all();
    }

    protected function createShippingLines(Model $model): array
    {
        if ($model->shipping_amount <= 0) {
            return [];
        }

        return [
            new LedgerEntry([
                'type' => LedgerEntryType::SHIPPING,
                'captured_at' => $this->getCapturedAt($model),
                'amount' => $model->shipping_amount,
                'supporter_id' => $this->getSupporterId($model),
            ]),
        ];
    }

    protected function createDCCLines(Model $model): array
    {
        return $this->getItems($model)->map(function (OrderItem $item) use ($model) {
            $dccAmount = $this->getDccAmountForItem($item, $model);

            if ($dccAmount <= 0) {
                return null;
            }

            return new LedgerEntry([
                'type' => LedgerEntryType::DCC,
                'captured_at' => $this->getCapturedAt($model),
                'amount' => $dccAmount,
                'item_id' => $item->getKey(),
                'sponsorship_id' => $item->sponsorship_id,
                'supporter_id' => $this->getSupporterId($model),
            ]);
        })->filter()->all();
    }

    protected function createTaxLines(Model $model): array
    {
        if ($model->taxtotal <= 0) {
            return [];
        }

        return [
            new LedgerEntry([
                'type' => LedgerEntryType::TAX,
                'captured_at' => $this->getCapturedAt($model),
                'amount' => $model->taxtotal,
                'supporter_id' => $this->getSupporterId($model),
            ]),
        ];
    }

    protected function modelIsLedgerable(Model $model): bool
    {
        return in_array(HasLedgerEntries::class, class_uses_recursive($model), true);
    }

    protected function saveEntries(Model $model, Collection $entries): Collection
    {
        $orderId = $model->getKey();

        if (is_a($model, Transaction::class)) {
            $orderId = $model->recurringPaymentProfile->productorder_id;
        }

        $entries = $entries->map(function (LedgerEntry $entry) use ($orderId) {
            $entry->order_id = $orderId;

            return $entry;
        });

        $model->ledgerEntries()->saveMany($entries);
        $model->refresh();

        return $model->ledgerEntries;
    }

    protected function getCapturedAt(Model $model): ?DateTime
    {
        if (is_a($model, Transaction::class)) {
            return $model->order_time;
        }

        if (is_a($model, Order::class)) {
            return $model->ordered_at ?? $model->confirmationdatetime;
        }

        return null;
    }

    protected function getItems(Model $model): Collection
    {
        if (is_a($model, Transaction::class)) {
            return collect([$model->recurringPaymentProfile->order_item]);
        }

        if (is_a($model, Order::class)) {
            return $model->items;
        }

        return collect();
    }

    protected function getSupporterId(Model $model): ?int
    {
        if (is_a($model, Transaction::class)) {
            return $model->recurringPaymentProfile->member_id;
        }

        if (is_a($model, Order::class)) {
            return $model->member_id;
        }

        return null;
    }

    protected function getDccAmountForItem(OrderItem $item, Model $model): float
    {
        if (is_a($model, Transaction::class)) {
            return $model->dcc_amount;
        }

        if (is_a($model, Order::class)) {
            return $item->dcc_amount;
        }

        return 0;
    }

    protected function isPaid(Model $model): bool
    {
        if (is_a($model, Transaction::class)) {
            return $model->payment_status === 'Completed';
        }

        if (is_a($model, Order::class)) {
            return $model->is_paid;
        }

        return false;
    }
}
