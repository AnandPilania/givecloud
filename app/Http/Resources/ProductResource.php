<?php

namespace Ds\Http\Resources;

use Ds\Domain\Commerce\Currency;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Ds\Models\Product */
class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $defaultCurrencyCode = Currency::getDefaultCurrencyCode();

        // This need to be first as min_variant_price will load relation.
        $variantLoaded = $this->whenLoaded('variants');

        return [
            'id' => $this->hashid,
            'code' => $this->code ?: null,
            'name' => $this->name ?: null,
            'description' => $this->summary ?: null,
            'feature_image' => $this->photo ? MediaResource::make($this->photo) : null,
            'url' => $this->abs_url ?: null,
            'price' => money($this->price, $this->base_currency),
            'price_min' => money($this->min_variant_price, $this->base_currency),
            'price_max' => money($this->max_variant_price, $this->base_currency),
            'donation_min' => money($this->min_price, $this->base_currency),
            'sale_price' => money($this->saleprice, $this->base_currency),
            'actual_price' => money($this->actual_price, $this->base_currency),
            'qty_available' => $this->available_for_purchase,
            'recurring_frequency' => [$this->recurringinterval],
            'recurring_schedule' => $this->recurring_type ?? sys_get('rpp_default_type'), // natural,fixed
            'recurring_with_dpo' => $this->recurring_with_dpo,
            'goal_amount' => money($this->goalamount, $defaultCurrencyCode),
            'goal_progress' => money($this->goal_progress, $defaultCurrencyCode),
            'goal_deadline' => $this->goal_deadline,
            'is_new' => (bool) $this->isnew,
            'is_sale' => (bool) $this->is_sale,
            'is_featured' => (bool) $this->isfeatured,
            'is_clearance' => (bool) $this->isclearance,
            'is_tax_receiptable' => (bool) $this->is_tax_receiptable,
            'cover_costs_enabled' => (bool) $this->is_dcc_enabled,
            'is_tribute' => (bool) $this->istribute,
            'enable_check_in' => (bool) $this->allow_check_in,
            'enable_out_of_stock' => (bool) $this->outofstock_allow,
            'enable_social_buttons' => (bool) $this->isfblike,
            'enable_tributes' => (bool) ($this->allow_tributes != 0),
            'tribute_notification_types' => (array) $this->tribute_notification_types,
            'require_tributes' => (bool) ($this->allow_tributes == 2),
            'ach_only' => (bool) $this->ach_only,
            'hide_price' => (bool) $this->hide_price,
            'hide_qty' => (bool) $this->hide_qty,
            'page_title' => $this->seo_pagetitle ?: null,
            'page_description' => $this->seo_pagedescription ?: null,
            'page_keywords' => $this->seo_pagekeywords ?: null,
            'page_content' => $this->description ?: null,
            'out_of_stock_message' => $this->outofstock_message ?: null,
            'primary_button_label' => $this->add_to_label ?: null,
            'alternate_button_label' => $this->alt_button_label ?: null,
            'alternate_button_url' => $this->alt_button_url ?: null,
            'created_at' => toUtcFormat($this->createddatetime, 'api'),
            'updated_at' => toUtcFormat($this->modifieddatetime, 'api'),
            'published_at' => toUtcFormat($this->publish_start_date, 'api'),
            'unpublish_at' => toUtcFormat($this->publish_end_date, 'api'),
            'meta' => [
                1 => $this->meta1 ?: null,
                2 => $this->meta2 ?: null,
                3 => $this->meta3 ?: null,
                4 => $this->meta4 ?: null,
                5 => $this->meta5 ?: null,
                6 => $this->meta6 ?: null,
                7 => $this->meta7 ?: null,
                8 => $this->meta8 ?: null,
                9 => $this->meta9 ?: null,
                10 => $this->meta10 ?: null,
                11 => $this->meta11 ?: null,
                12 => $this->meta12 ?: null,
                13 => $this->meta13 ?: null,
                14 => $this->meta14 ?: null,
                15 => $this->meta15 ?: null,
                16 => $this->meta16 ?: null,
                17 => $this->meta17 ?: null,
                18 => $this->meta18 ?: null,
                19 => $this->meta19 ?: null,
                20 => $this->meta20 ?: null,
                21 => $this->meta21 ?: null,
                22 => $this->meta22 ?: null,
                23 => $this->meta23 ?: null,
            ],
            'default_donation_amount' => null,
            'default_donation_options' => null,
            'default_recurring_donation_options' => null,
            // Relationships
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'variants' => VariantResource::collection($variantLoaded),
        ];
    }
}
