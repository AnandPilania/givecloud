<?php

namespace Ds\Domain\Webhook\Transformers;

use Ds\Models\OrderItem;
use Ds\Models\RecurringPaymentProfile;
use League\Fractal\TransformerAbstract;

class OrderItemTransformer extends TransformerAbstract
{
    /** @var array */
    protected $defaultIncludes = [
        'sponsorship',
        'fields',
    ];

    /**
     * @param \Ds\Models\OrderItem $item
     * @return array
     */
    public function transform(OrderItem $item)
    {
        if ($item->sponsorship) {
            $data = [
                'id' => (int) $item->id,
                'code' => $item->sponsorship->reference_number,
                'name' => $item->sponsorship->full_name,
                'price' => number_format($item->price, 2, '.', ''),
                'quantity' => (int) $item->qty,
                'total' => number_format($item->total, 2, '.', ''),
                'total_discount' => 0.0,
                'promo' => $item->promocode ?: null,
                'weight' => 0.0,
                'is_shippable' => false,
                'vendor_txn_id' => $item->alt_transaction_id,
            ];
        } else {
            $data = [
                'id' => (int) $item->id,
                'product_id' => nullable_cast('int', $item->variant->productid ?: null),
                'variant_id' => nullable_cast('int', $item->variant->id ?: null),
                'code' => $item->code ?? null,
                'name' => $item->variant->product->name ?? null,
                'variant_title' => $item->variant->variantname,
                'price' => number_format($item->price, 2, '.', ''),
                'quantity' => (int) $item->qty,
                'total' => number_format($item->total, 2, '.', ''),
                'total_discount' => number_format(($item->variant->price * $item->qty) - $item->total, 2, '.', ''),
                'promo' => $item->promocode ?: null,
                'weight' => $item->variant->isshippable ? (float) $item->variant->weight : 0.0,
                'is_shippable' => (bool) $item->variant->isshippable,
                'vendor_txn_id' => $item->alt_transaction_id,
            ];
        }

        if ($item->is_recurring) {
            $profile = new RecurringPaymentProfile;
            $profile->billing_period = $item->recurring_frequency;

            $startDate = $profile->getFirstPossibleStartDate(
                $item->variant->product->recurring_type ?? sys_get('rpp_default_type'),
                $item->recurring_day,
                $item->recurring_day_of_week,
                $item->recurring_with_initial_charge ? 'one-time' : null,
                $item->recurring_starts_on,
                $item->order()->first()->confirmationdatetime ?? 'today'
            );

            $data['recurring'] = [
                'amount' => number_format($item->recurring_amount, 2, '.', ''),
                'period' => (int) $profile->billing_frequency,
                'frequency' => $profile->billing_period,
                'day' => $item->recurring_day ?: null,
                'day_of_week' => $item->recurring_day_of_week ?: null,
                'start_date' => toUtcFormat($startDate, 'json'),
                'with_initial_charge' => (bool) $item->recurring_with_initial_charge,
                'with_dpo' => (bool) $item->recurring_with_dpo,
            ];
        }

        if ($item->is_tribute) {
            $data['tribute'] = [
                'name' => $item->tribute_name ?: null,
                'tribute_type_id' => (int) $item->tribute_type_id ?: null,
                'vendor_tribute_id' => $item->dpo_tribute_id ?: null,
                'notification' => null,
            ];

            if ($item->tribute_notify) {
                $data['tribute']['notification'] = [
                    'type' => $item->tribute_notify,
                    'name' => $item->tribute_notify_name ?: null,
                    'message' => $item->tribute_notify_message ?: null,
                    'sent_on' => toUtcFormat($item->tribute_notify_at, 'Y-m-d'),
                    'email' => $item->tribute_notify_email ?: null,
                    'address' => $item->tribute_notify_address ?: null,
                    'city' => $item->tribute_notify_city ?: null,
                    'state' => $item->tribute_notify_state ?: null,
                    'zip' => $item->tribute_notify_zip ?: null,
                    'country' => $item->tribute_notify_country ?: null,
                ];
            }
        }

        $data['accounting'] = [
            'code' => $item->gl_code ?: null,
        ];

        return $data;
    }

    /**
     * @param \Ds\Models\OrderItem $item
     * @return \League\Fractal\Resource\Item|void
     */
    public function includeSponsorship(OrderItem $item)
    {
        if ($item->sponsorship) {
            return $this->item($item->sponsorship, new SponsorshipTransformer);
        }
    }

    /**
     * @param \Ds\Models\OrderItem $item
     * @return \League\Fractal\Resource\Collection
     */
    public function includeFields(OrderItem $item)
    {
        return $this->collection($item->fields, new ProductCustomFieldTransformer);
    }
}
