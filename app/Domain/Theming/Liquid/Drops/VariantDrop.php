<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;
use Ds\Models\Product;
use Illuminate\Support\Str;

class VariantDrop extends Drop
{
    protected function initialize($source)
    {
        $this->liquid = [
            'id' => $source->id,
            'code' => $source->sku ?: $source->product->code ?: null,
            'title' => $source->variantname,
            'price' => $this->toCurrency($source->total_linked_price ? ($source->total_linked_price) : $source->price),
            'price_presets' => $source->price_presets,
            'sale_price' => $this->toCurrency($source->total_linked_saleprice ? ($source->total_linked_saleprice) : $source->saleprice),
            'minimum_price' => $source->price_minimum,
            'on_sale' => $source->is_sale,
            'is_default' => $source->isdefault,
            'is_shippable' => $source->isshippable,
            'is_donation' => (bool) $source->is_donation,
            'billing_period' => $source->billing_period,
            'inventory_management' => 'givecloud',
            'inventory_policy' => $source->product->outofstock_allow ? 'continue' : 'deny',
            'inventory_quantity' => $source->quantity,
            'shipping_expectation' => $source->shipping_expectation,
            'fair_market_value' => $this->toCurrency($source->fair_market_value),
            'sequence' => $source->sequence,
            'weight' => $source->weight,
        ];
    }

    public function available()
    {
        return $this->liquid['inventory_policy'] === 'continue' || $this->liquid['inventory_quantity'] > 0;
    }

    public function currency()
    {
        return cart()->currency;
    }

    public function feature_image()
    {
        return $this->images()->first();
    }

    public function metadata()
    {
        return collect($this->source->metadata)->filter(function ($value, $key) {
            return Str::startsWith($key, 'meta');
        });
    }

    public function product()
    {
        // Only return product info when we explicitly ask for it to avoid an infinite recursion loop
        return (isset($this->overrides['includeProduct']) && $this->overrides['includeProduct']) ? new ProductDrop($this->source->product) : null;
    }

    public function images()
    {
        return $this->source->media()->images()->get();
    }

    public function recurring_first_payment()
    {
        return $this->source->billing_starts_on ? false : true;
    }

    public function redirects_to()
    {
        $product = $this->source->metadata['redirects_to'];

        if ($product) {
            return data_get(Product::find($product), 'abs_url');
        }
    }

    private function toCurrency($amount)
    {
        static $currency;

        if (empty($currency)) {
            $currency = $this->currency();
        }

        return money($amount, $this->source->product->base_currency)->toCurrency($currency)->getAmount();
    }
}
