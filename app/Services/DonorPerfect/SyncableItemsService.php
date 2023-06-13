<?php

namespace Ds\Services\DonorPerfect;

use Ds\Models\Order;
use Ds\Models\OrderItem;
use Ds\Models\Product;
use Illuminate\Support\Collection;

class SyncableItemsService
{
    public function hasSyncableItems(Order $order): bool
    {
        return $this->syncableItems($order)->isNotEmpty();
    }

    public function orderHasSyncableItemsWithDcc(Order $order): bool
    {
        return $this->syncableItems($order)->filter(function (OrderItem $orderItem) {
            return $orderItem->is_eligible_for_dcc && $orderItem->dcc_amount;
        })->isNotEmpty();
    }

    public function orderHasSyncableShippableItems(Order $order): bool
    {
        return $this->syncableItems($order)->filter(function (OrderItem $orderItem) {
            return $orderItem->requires_shipping;
        })->isNotEmpty();
    }

    public function orderHasSyncableTaxableItems(Order $order): bool
    {
        if ((sys_get('shipping_taxes_apply') || sys_get('taxcloud_api_key')) && $order->shipping_amount > 0) {
            return true;
        }

        return $this->syncableItems($order)->filter(function (OrderItem $orderItem) {
            if (sys_get('taxcloud_api_key')) {
                return isset($orderItem->variant->product->taxcloud_tic_id);
            }

            return $orderItem->taxes->isNotEmpty();
        })->isNotEmpty();
    }

    public function productIsSyncable(Product $product): bool
    {
        return (bool) $product->metadata('dp_syncable', true);
    }

    public function itemIsSyncable(OrderItem $orderItem): bool
    {
        if ($orderItem->sponsorship_id) {
            return true;
        }

        if (! $orderItem->variant) {
            return false;
        }

        return $this->productIsSyncable($orderItem->variant->product);
    }

    public function syncableItems(Order $order): Collection
    {
        return $order->items->filter(function (OrderItem $orderItem) {
            return $this->itemIsSyncable($orderItem);
        });
    }
}
