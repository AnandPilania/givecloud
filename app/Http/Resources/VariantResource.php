<?php

namespace Ds\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Ds\Models\Variant */
class VariantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        // We need to do this first because $this->product->... with load the product.
        $loadedProduct = $this->whenLoaded('product');

        return [
            'id' => $this->hashid,
            'code' => $this->sku ?: $this->product->code ?: null,
            'title' => $this->variantname,
            'images' => MediaResource::collection($this->media()->images()->get()),
            'price' => money($this->total_linked_price ? ($this->total_linked_price) : $this->price, $this->product->base_currency),
            'price_presets' => $this->price_presets,
            'sale_price' => money($this->total_linked_saleprice ? ($this->total_linked_saleprice) : $this->saleprice, $this->product->base_currency),
            'minimum_price' => $this->price_minimum,
            'on_sale' => $this->is_sale,
            'is_default' => $this->isdefault,
            'is_shippable' => $this->isshippable,
            'is_donation' => (bool) $this->is_donation,
            'billing_period' => $this->billing_period,
            'inventory_management' => 'givecloud',
            'inventory_policy' => $this->product->outofstock_allow ? 'continue' : 'deny',
            'inventory_quantity' => $this->quantity,
            'inventory_updated_at' => toUtcFormat($this->quantitymodifieddatetime, 'api'),
            'shipping_expectation' => $this->shipping_expectation,
            'fair_market_value' => money($this->fair_market_value, $this->product->base_currency),
            'sequence' => $this->sequence,
            'weight' => $this->weight,
            'created_at' => toUtcFormat($this->created_at, 'api'),
            'updated_at' => toUtcFormat($this->updated_at, 'api'),
            // Relationships
            'product' => ProductResource::make($loadedProduct),
        ];
    }
}
