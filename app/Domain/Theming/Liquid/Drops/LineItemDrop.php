<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;
use Ds\Models\Tribute;

class LineItemDrop extends Drop
{
    protected $mutators = [
        'is_donation',
        'variant',
    ];

    /** @var \Ds\Models\OrderItem */
    protected $source;

    /**
     * @param \Ds\Models\OrderItem $source
     */
    protected function initialize($source)
    {
        $this->liquid = [
            'id' => (int) $source->id,
            'form_fields' => [],
            'is_locked' => $source->is_locked,
            'line_price' => (float) $source->total,
            'name' => $source->description,
            'is_discounted' => $source->is_price_reduced,
            'original_line_price' => (float) $source->undiscounted_price * $source->qty,
            'original_price' => (float) $source->undiscounted_price,
            'price' => (float) $source->price,
            'quantity' => (float) $source->qty,
            'fundraising_form_upgrade' => $source->is_fundraising_form_upgrade,
            'recurring_amount' => nullable_cast('float', $source->recurring_amount),
            'recurring_day' => $source->recurring_day,
            'recurring_day_of_week' => $source->recurring_day_of_week,
            'recurring_description' => $source->recurring_description,
            'recurring_frequency' => $source->recurring_frequency,
            'requires_ach' => $source->requires_ach,
            'requires_shipping' => false,
            'sku' => $source->reference,
            'thumbnail' => $source->image_thumb,
            'total' => (float) $source->total,
            'total_discount' => 0,
            'type' => ($source->sponsorship_id) ? 'sponsorship' : 'product',
            'url' => $source->public_url,
            'weight' => 0,
            'cover_costs_enabled' => (bool) $source->dcc_eligible,
            'cover_costs_amount' => (float) $source->dcc_amount,
            'cover_costs_recurring_amount' => (float) $source->dcc_recurring_amount,
        ];

        if ($source->variant) {
            $this->liquid = array_merge($this->liquid, [
                'product_id' => (int) $source->variant->productid,
                'requires_shipping' => (bool) $source->variant->isshippable,
                'total_discount' => ($source->variant->price * $source->qty) - $source->total,
                'variant_id' => (int) $source->variant->id,
                'variant_title' => $source->variant->variantname,
                'weight' => (float) $source->variant->isshippable ? $source->variant->weight : 0,
            ]);

            if ($source->lockedItems->count() > 0) {
                $original_total = $source->undiscounted_price + $source->lockedItems->sum('undiscounted_price');

                $this->liquid = array_merge($this->liquid, [
                    'original_line_price' => (float) $original_total * $source->qty,
                    'original_price' => (float) $original_total,
                    'line_price' => (float) $source->locked_variants_total,
                    'total' => (float) $source->locked_variants_total,
                    'price' => (float) $source->locked_variants_price,
                ]);
            }

            $source->load('fields');

            // custom field values
            foreach ($source->fields as $field) {
                $this->liquid['form_fields'][] = [
                    'field' => new FieldDrop($field),
                    'value' => $field->value_formatted,
                ];
            }
        }
    }

    public function recurring_frequency_short()
    {
        switch ($this->source->recurring_frequency) {
            case 'weekly':    return 'wk';
            case 'biweekly':  return 'bi-wk';
            case 'monthly':   return 'mth';
            case 'quarterly': return 'qr';
            case 'biannually': return 'bi-yr';
            case 'annually':  return 'yr';
        }
    }

    public function discounts()
    {
        $discounts = [];

        if ($this->source->promo) {
            $discounts[] = new DiscountDrop($this->source->promo, $this->source->order, $this->source);
        }

        return $discounts;
    }

    public function gl_code()
    {
        return $this->source->gl_code;
    }

    public function metadata()
    {
        return $this->source->metadata;
    }

    public function shipping_expectation()
    {
        if ($this->source->variant) {
            return $this->source->variant->shipping_expectation;
        }
    }

    public function quantity_editable()
    {
        if ($this->source->is_donation || $this->source->is_locked) {
            return false;
        }

        if ($this->source->variant->product->hide_qty ?? false) {
            return false;
        }

        return true;
    }

    public function product()
    {
        if ($this->source->variant) {
            return $this->source->variant->product;
        }
    }

    public function tribute()
    {
        if ($this->source->is_tribute && ! $this->source->tribute) {
            $tribute = new Tribute([
                'name' => $this->source->tribute_name,
                'amount' => ($this->source->is_recurring ? $this->source->recurring_amount : $this->source->price) * $this->source->qty,
                'message' => $this->source->tribute_message,
                'notify_name' => $this->source->tribute_notify_name,
                'notify_email' => $this->source->tribute_notify_email,
                'notify_address' => $this->source->tribute_notify_address,
                'notify_city' => $this->source->tribute_notify_city,
                'notify_state' => $this->source->tribute_notify_state,
                'notify_zip' => $this->source->tribute_notify_zip,
                'notify_country' => $this->source->tribute_notify_country,
                'notify' => $this->source->tribute_notify,
                'notify_at' => $this->source->tribute_notify_at,
            ]);

            return $tribute->setRelation('tributeType', $this->source->tributeType);
        }

        return $this->source->tribute;
    }
}
