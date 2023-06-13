<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;
use Ds\Http\Resources\DonationForms\DonationFormResource;

class CheckoutDrop extends Drop
{
    /** @var \Ds\Models\Order */
    protected $source;

    /**
     * @param \Ds\Models\Order $source
     */
    protected function initialize($source)
    {
        $this->liquid = [
            'id' => $source->client_uuid,
            'meta_contribution_id' => $source->contribution_id,
            'comments' => $source->comments,
            'discounts_amount' => $source->total_savings,
            'discounts_savings' => 0 - $source->total_savings,
            'downloadable_item_count' => $source->download_items,
            'email' => $source->billingemail,
            'name' => "#{$source->id}",
            'order_id' => $source->id,
            'order_name' => "#{$source->client_uuid}",
            'order_number' => $source->client_uuid,
            'currency' => $source->currency,
            'recurring_item_count' => $source->recurring_items,
            'referral_source' => $source->referral_source,
            'ship_to_billing' => $source->ship_to_billing,
            'shippable_item_count' => $source->shippable_items,
            'shipping_method' => $source->shipping_method_name,
            'shipping_method_value' => $source->shipping_method_id ?: $source->courier_method,
            'eligible_for_free_shipping' => $source->is_free_shipping,
            'shipping_price' => $source->shipping_amount,
            'subtotal_price' => $source->subtotal,
            'tax_price' => $source->taxtotal,
            'total_price' => $source->totalamount,
            'total_price_in_subunits' => money($source->totalamount, $source->currency_code)->getAmountInSubunits(),
            'payment_type' => $source->payment_type,
            'cover_costs_enabled' => $source->dcc_enabled_by_customer,
            'cover_costs_amount' => $source->dcc_total_amount,
            'cover_costs_type' => $source->dcc_type,
            'payment_method_saved' => (bool) $source->vault_id,
        ];
    }

    public function account()
    {
        return $this->source->member;
    }

    public function billing_address()
    {
        return new AddressDrop($this->source, 'billing');
    }

    public function buyer_accepts_marketing()
    {
        if ($this->source->member) {
            return (bool) $this->source->member->email_opt_in;
        }

        return false;
    }

    public function discounts()
    {
        $discounts = [];

        foreach ($this->source->promoCodes as $promoCode) {
            $discounts[] = new DiscountDrop($promoCode, $this->source);
        }

        return $discounts;
    }

    public function line_items()
    {
        return $this->source->items;
    }

    public function requires_ach()
    {
        return $this->source->items->reduce(function ($carry, $item) {
            return $carry || $item->requires_ach;
        });
    }

    public function requires_captcha()
    {
        return $this->source->requiresCaptcha();
    }

    public function requires_payment()
    {
        return $this->source->totalamount > 0 || $this->source->recurring_items > 0;
    }

    public function requires_shipping()
    {
        return $this->source->shippable_items > 0;
    }

    public function share_links(): ?array
    {
        if (! $this->source->isForFundraisingForm()) {
            return null;
        }

        $product = $this->source->items[0]->variant->product ?? null;

        return DonationFormResource::make($product)->getShareLinks();
    }

    public function shipping_address()
    {
        return new AddressDrop($this->source, 'shipping');
    }

    public function shipping_expectations()
    {
        return $this->source->items->filter(function ($item) {
            return empty($item->shipping_expectation);
        })->isNotEmpty();
    }

    public function shipping_methods()
    {
        $methods = [];

        if ($this->source->is_free_shipping) {
            $methods[] = new ShippingMethodDrop([
                'name' => 'FREE shipping',
                'free' => true,
            ]);
        } else {
            foreach ($this->source->available_shipping_methods as $method) {
                $methods[] = new ShippingMethodDrop(get_object_vars($method));
            }
        }

        return $methods;
    }

    public function tax_lines()
    {
        $lines = [];

        foreach ($this->source->tax_lines as $line) {
            $lines[] = new TaxLineDrop((array) $line);
        }

        return $lines;
    }
}
