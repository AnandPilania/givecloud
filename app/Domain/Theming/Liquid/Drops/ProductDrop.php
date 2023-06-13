<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Commerce\Currency;
use Ds\Domain\Theming\Liquid\Drop;
use Ds\Services\SocialLinkService;

class ProductDrop extends Drop
{
    /** @var array */
    protected $serializationBlacklist = ['social_proof'];

    protected $mutators = [
        'categories',
        'variants',
        'default_variant',
        'goal_days_left',
        'goal_progress_percent',
        'tribute_types',
    ];

    protected function initialize($source)
    {
        $this->liquid = [
            'id' => (int) $source->id,
            'code' => $source->code ?: null,
            'name' => $source->name ?: null,
            'description' => $source->summary ?: null,
            'url' => $source->abs_url ?: null,
            'share_url' => $source->share_url ?: null,
            'social_urls' => SocialLinkService::generate($source->share_url, $source->name, ($source->photo ? $source->photo->thumbnail_url : null), $source->summary),
            'price' => $this->toCurrency($source->price),
            'price_min' => $this->toCurrency($source->min_variant_price),
            'price_max' => $this->toCurrency($source->max_variant_price),
            'donation_min' => $this->toCurrency($source->min_price),
            'sale_price' => $this->toCurrency($source->saleprice),
            'actual_price' => $this->toCurrency($source->actual_price),
            'qty_available' => $source->available_for_purchase,
            'recurring_frequency' => [$source->recurringinterval],
            'recurring_schedule' => $source->recurring_type ?? sys_get('rpp_default_type'), // natural,fixed
            'recurring_with_dpo' => $source->recurring_with_dpo,
            'goal_amount' => $this->toCurrency($source->goalamount),
            'goal_progress' => $this->toCurrency($source->goal_progress, Currency::getDefaultCurrencyCode()),
            'goal_deadline' => $source->goal_deadline,
            'is_new' => (bool) $source->isnew,
            'is_sale' => (bool) $source->is_sale,
            'is_featured' => (bool) $source->isfeatured,
            'is_clearance' => (bool) $source->isclearance,
            'is_tax_receiptable' => (bool) $source->is_tax_receiptable,
            'cover_costs_enabled' => (bool) $source->is_dcc_enabled,
            'is_tribute' => (bool) $source->istribute,
            'enable_check_in' => (bool) $source->allow_check_in,
            'enable_out_of_stock' => (bool) $source->outofstock_allow,
            'enable_social_buttons' => (bool) $source->isfblike,
            'enable_tributes' => (bool) ($source->allow_tributes != 0),
            'tribute_notification_types' => (array) $source->tribute_notification_types,
            'require_tributes' => (bool) ($source->allow_tributes == 2),
            'require_email_tribute' => $source->allow_tributes === 2 && $source->tribute_notification_types && count($source->tribute_notification_types) === 1 && in_array('email', $source->tribute_notification_types, true),
            'require_letter_tribute' => $source->allow_tributes === 2 && $source->tribute_notification_types && count($source->tribute_notification_types) === 1 && in_array('letter', $source->tribute_notification_types, true),
            'require_some_tribute' => $source->allow_tributes === 2 && $source->tribute_notification_types && count($source->tribute_notification_types) === 2,
            'ach_only' => (bool) $source->ach_only,
            'hide_price' => (bool) $source->hide_price,
            'hide_qty' => (bool) $source->hide_qty,
            'page_title' => $source->seo_pagetitle ?: null,
            'page_description' => $source->seo_pagedescription ?: null,
            'page_keywords' => $source->seo_pagekeywords ?: null,
            'page_content' => $source->description ?: null,
            'out_of_stock_message' => $source->outofstock_message ?: null,
            'filter' => $source->author ?: null,
            'primary_button_label' => $source->add_to_label ?: null,
            'alternate_button_label' => $source->alt_button_label ?: null,
            'alternate_button_url' => $source->alt_button_url ?: null,
            'created_at' => toUtcFormat($source->createddatetime, 'api'),
            'updated_at' => toUtcFormat($source->modifieddatetime, 'api'),
            'published_at' => toUtcFormat($source->publish_start_date, 'api'),
            'unpublish_at' => toUtcFormat($source->publish_end_date, 'api'),
            'meta' => [
                1 => $source->meta1 ?: null,
                2 => $source->meta2 ?: null,
                3 => $source->meta3 ?: null,
                4 => $source->meta4 ?: null,
                5 => $source->meta5 ?: null,
                6 => $source->meta6 ?: null,
                7 => $source->meta7 ?: null,
                8 => $source->meta8 ?: null,
                9 => $source->meta9 ?: null,
                10 => $source->meta10 ?: null,
                11 => $source->meta11 ?: null,
                12 => $source->meta12 ?: null,
                13 => $source->meta13 ?: null,
                14 => $source->meta14 ?: null,
                15 => $source->meta15 ?: null,
                16 => $source->meta16 ?: null,
                17 => $source->meta17 ?: null,
                18 => $source->meta18 ?: null,
                19 => $source->meta19 ?: null,
                20 => $source->meta20 ?: null,
                21 => $source->meta21 ?: null,
                22 => $source->meta22 ?: null,
                23 => $source->meta23 ?: null,
            ],
            'default_donation_amount' => null,
            'default_donation_options' => null,
            'default_recurring_donation_options' => null,
        ];
    }

    public function metadata()
    {
        return $this->source->metadata;
    }

    public function categories()
    {
        return Drop::collectionFactory($this->source->categories, 'Category', [
            'products' => [],
        ]);
    }

    public function currency()
    {
        return cart()->currency;
    }

    public function designation_options()
    {
        if ($this->source->template_suffix === 'page-with-payment') {
            return $this->source->designation_options;
        }
    }

    public function social_proof()
    {
        $social_proof = [];

        $items = $this->source->paidOrderItems()
            ->whereNull('productorder.refunded_at')
            ->orderBy('id', 'desc')
            ->with('order')
            ->take(80)
            ->get();

        foreach ($items as $orderItem) {
            $social_proof[] = new SocialProofDrop($orderItem);
        }

        return $social_proof;
    }

    public function feature_image()
    {
        return $this->source->photo ?? null;
    }

    public function form_fields()
    {
        return $this->source->customFields;
    }

    public function has_visible_form_fields(): bool
    {
        return $this->source->customFields->filter(function ($field) {
            return $field->type !== 'hidden';
        })->isNotEmpty();
    }

    public function recurring_first_payment()
    {
        if ($this->liquid['recurring_schedule'] === 'fixed') {
            return volt_setting('product_recurring_first_payment');
        }
    }

    public function on_sale()
    {
        return $this->source->variants->where('is_sale', true)->isNotEmpty();
    }

    private function toCurrency($amount, $from = null)
    {
        static $currency;

        if (empty($currency)) {
            $currency = $this->currency();
        }

        return money($amount, $from ?? $this->source->base_currency)
            ->toCurrency($currency)
            ->getAmount();
    }
}
