<?php

namespace Ds\Services\Order;

use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Models\Order;
use Ds\Models\Variant;
use Illuminate\Database\Eloquent\Collection;

class OrderAddItemLogicValidationService
{
    /**
     * @throws \Ds\Domain\Shared\Exceptions\MessageException
     */
    public function checkIfOrderPaidAlready(Order $order): void
    {
        if ($order->is_paid) {
            throw new MessageException("Unable to add items after a contribution has been paid (Contribution: {$order->client_uuid})");
        }
    }

    /**
     * @throws \Ds\Domain\Shared\Exceptions\MessageException
     */
    public function checkDonationReachesMinimumAmount(Order $order, Variant $variant, float $amount): void
    {
        if ($variant->is_donation && $variant->price_minimum > $amount) {
            throw new MessageException('Must be greater than ' . money($variant->price_minimum, $order->currency));
        }
    }

    /**
     * @throws \Ds\Domain\Shared\Exceptions\MessageException
     */
    public function validateAllVariants(Collection $variants, Collection $orderItems, int $quantity): void
    {
        $variants
            ->filter->product
            ->each(function ($variant) use ($orderItems, $quantity) {
                $this->checkItemsAreAvailable($orderItems, $variant, $quantity);
                $this->checkMembershipConflicts($orderItems, $variant);
            });
    }

    /**
     * @throws \Ds\Domain\Shared\Exceptions\MessageException
     */
    protected function checkItemsAreAvailable(Collection $items, Variant $variant, int $quantity): void
    {
        $quantityInCart = $items
            ->filter(function ($item) use ($variant) {
                return $item->variant && $item->variant->getKey() === $variant->getKey();
            })->sum('qty');

        // include what's already in the cart when checking availability
        if (! $variant->checkAvailability($quantityInCart + $quantity)) {
            if ($variant->maximumQuantityAvailableForPurchase > 0) {
                throw new MessageException(trans_choice('frontend/cart.limited_stock', $variant->maximumQuantityAvailableForPurchase));
            }

            throw new MessageException(trans('frontend/cart.no_stock'));
        }
    }

    /**
     * @throws \Ds\Domain\Shared\Exceptions\MessageException
     */
    protected function checkMembershipConflicts(Collection $orderItems, Variant $variant): void
    {
        if (! $variant->membership_id || ! dpo_is_enabled()) {
            return;
        }

        $conflicts = $orderItems
            ->filter(function ($item) {
                return $item->variant && $item->variant->membership_id !== null;
            })->reject(function ($item) use ($variant) {
                return $item->variant->membership_id === $variant->membership_id;
            });

        if ($conflicts->isNotEmpty()) {
            throw new MessageException('You can only purchase one type of membership.');
        }
    }
}
