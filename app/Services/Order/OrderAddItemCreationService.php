<?php

namespace Ds\Services\Order;

use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Models\Order;
use Ds\Models\OrderItem;
use Ds\Models\Variant;
use Illuminate\Database\Eloquent\Collection;

class OrderAddItemCreationService
{
    /** @var \Ds\Models\OrderItem */
    protected $orderItem;

    /** @var \Ds\Services\Order\OrderAddItemLogicValidationService */
    protected $orderAddItemLogicValidationService;

    public function __construct(OrderAddItemLogicValidationService $orderAddItemLogicValidationService)
    {
        $this->orderAddItemLogicValidationService = $orderAddItemLogicValidationService;
    }

    /**
     * @throws \Ds\Domain\Shared\Exceptions\MessageException
     */
    public function create(Order $order, Variant $variant, OrderAddItemRequest $request): OrderItem
    {
        $this->orderItem = $this->buildNewOrderItem($order->getKey(), $variant->getKey(), $request);

        $this->setOrderItemPrice($order, $variant, $request->amt);

        if ($request->recurring_frequency) {
            $this->setOrderItemRecurringDetails($order, $variant, $request);
        }

        if ($this->orderItem->is_tribute) {
            $this->setOrderItemTributeDetails($request);
        }

        if ($request->fundraising_page_id) {
            $this->setOrderItemFundraisingPage($request->fundraising_page_id, $request->fundraising_member_id);
        }

        $this->orderItem->original_price = $this->orderItem->price;

        $this->setOrderItemDiscount($variant);

        if (count($request->metadata)) {
            $this->orderItem->metadata($request->metadata);
        }

        if (! $this->orderItem->save()) {
            throw new MessageException('An error occured while adding item to cart.');
        }

        $this->createLinkedVariantOrderItems($order->getKey(), $variant->linkedVariants, $request->qty);

        if (count($request->fields)) {
            $this->orderItem->fields()->attach($this->getCustomFields($request));
        }

        return $this->orderItem;
    }

    protected function buildNewOrderItem(int $orderId, int $variantId, OrderAddItemRequest $request): OrderItem
    {
        $orderItem = new OrderItem;
        $orderItem->productorderid = $orderId;
        $orderItem->productinventoryid = $variantId;
        $orderItem->qty = $request->qty;
        $orderItem->is_tribute = $request->is_tribute;
        $orderItem->public_message = $request->public_message;
        $orderItem->gift_aid = $request->gift_aid;
        $orderItem->gl_code = $request->gl_code;

        return $orderItem;
    }

    protected function createLinkedVariantOrderItems(int $orderId, Collection $variants, int $quantity): void
    {
        foreach ($variants as $linkedVariant) {
            $linkedItem = new OrderItem;
            $linkedItem->productorderid = $orderId;
            $linkedItem->productinventoryid = $linkedVariant->getKey();
            $linkedItem->price = $linkedVariant->pivot->price;
            $linkedItem->original_price = $linkedVariant->pivot->price;
            $linkedItem->qty = $linkedVariant->pivot->qty * $quantity;
            $linkedItem->locked_to_item_id = $this->orderItem->getKey();
            $linkedItem->save();
        }
    }

    /**
     * passed in as either:
     * a) fields[99] = 'value'
     * b) fields[99] = ['id' => 99, 'value' => 'test'] << this is WEIRD
     */
    protected function getCustomFields(OrderAddItemRequest $request): array
    {
        return (new Collection($request->fields))->mapWithKeys(function ($field, $id) {
            $value = $this->isCustomFieldAnArray($field) ? ($field['value'] ?? []) : $field;
            $value = array_filter((array) $value, 'strlen'); // remove null and empty values

            return [$id => ['value' => implode(',', $value)]];
        })->toArray();
    }

    /**
     * Test if the given custom field is a plain value or a nested array.
     */
    protected function isCustomFieldAnArray($field): bool
    {
        return is_array($field) && (array_key_exists('id', $field) || array_key_exists('value', $field));
    }

    protected function isRppDonorPerfect(): bool
    {
        return sys_get('rpp_donorperfect') == 1;
    }

    protected function setOrderItemDiscount(Variant $variant): void
    {
        $this->orderItem->discount = $variant && $variant->is_sale
            ? $variant->price - $variant->saleprice
            : 0;
    }

    protected function setOrderItemFundraisingPage(int $fundraisingPageId, ?int $fundraisingMemberId): void
    {
        $this->orderItem->fundraising_page_id = $fundraisingPageId;
        $this->orderItem->fundraising_member_id = $fundraisingMemberId;
    }

    protected function setOrderItemPrice(Order $order, Variant $variant, $amount): void
    {
        $this->orderItem->price = $variant->is_donation
            ? $amount
            : money($variant->actual_price, $variant->product->base_currency)
                ->toCurrency($order->currency_code)
                ->getAmount();
    }

    protected function setOrderItemRecurringDetails(Order $order, Variant $variant, OrderAddItemRequest $request): void
    {
        $this->orderItem->recurring_amount = $this->orderItem->price;
        $this->orderItem->recurring_frequency = $request->recurring_frequency;
        $this->orderItem->recurring_with_dpo = $this->isRppDonorPerfect();
        $this->orderItem->recurring_cycles = $variant->total_billing_cycles;
        $this->orderItem->recurring_starts_on = $variant->billing_starts_on;
        $this->orderItem->recurring_ends_on = $variant->billing_ends_on;

        $this->setOrderItemRecurringInitialChargeAndDays($order, $request, sys_get('rpp_default_type') === 'natural');

        if (! $this->orderItem->recurring_with_initial_charge) {
            $this->orderItem->price = 0.00;
        }
    }

    protected function setOrderItemRecurringInitialChargeAndDays(Order $order, OrderAddItemRequest $request, bool $isNaturalRpp): void
    {
        if ($this->orderItem->recurring_starts_on) {
            $this->orderItem->recurring_with_initial_charge = false;
        } else {
            $this->orderItem->recurring_with_initial_charge = $isNaturalRpp ? true : $request->recurring_with_initial_charge;
        }

        if (in_array($this->orderItem->recurring_frequency, ['weekly', 'biweekly'])) {
            $this->orderItem->recurring_day_of_week = $isNaturalRpp ? $order->started_at->toLocal()->dayOfWeek : $request->recurring_day_of_week;
        } else {
            $this->orderItem->recurring_day = $isNaturalRpp ? $order->started_at->toLocal()->day : $request->recurring_day;
        }
    }

    protected function setOrderItemTributeDetails(OrderAddItemRequest $request): void
    {
        $this->orderItem->dpo_tribute_id = $request->dpo_tribute_id;
        $this->orderItem->tribute_type_id = $request->tribute_type_id;
        $this->orderItem->tribute_name = $request->tribute_name;

        if ($request->tribute_notify ?? false) {
            $this->orderItem->tribute_notify = $request->tribute_notify;
            $this->orderItem->tribute_notify_name = $request->tribute_notify_name;
            $this->orderItem->tribute_notify_at = $request->tribute_notify_at ?: null;
            $this->orderItem->tribute_message = $request->tribute_message ?: null;
        }

        if ($this->orderItem->tribute_notify === 'email') {
            $this->orderItem->tribute_notify_email = $request->tribute_notify_email;
        }

        if ($this->orderItem->tribute_notify === 'letter') {
            $this->orderItem->tribute_notify_address = $request->tribute_notify_address;
            $this->orderItem->tribute_notify_city = $request->tribute_notify_city;
            $this->orderItem->tribute_notify_state = $request->tribute_notify_state;
            $this->orderItem->tribute_notify_zip = $request->tribute_notify_zip;
            $this->orderItem->tribute_notify_country = $request->tribute_notify_country;
        }
    }
}
