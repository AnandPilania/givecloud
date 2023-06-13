<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;
use Ds\Models\OrderItem;
use Ds\Models\Pledge;
use Illuminate\Support\Str;

class SocialProofDrop extends Drop
{
    protected function initialize($source)
    {
        if (is_instanceof($source, OrderItem::class)) {
            $this->initializeFromOrderItem();
        } elseif (is_instanceof($source, Pledge::class)) {
            $this->initializeFromPledge();
        }
    }

    protected function initializeFromOrderItem()
    {
        $this->liquid = [
            'id' => $this->source->order->invoicenumber,
            'type' => $this->is_donation ? 'donation' : 'purchase',
            'name' => $this->source->order->is_anonymous ? null : trim($this->source->order->billing_first_name . ' ' . $this->source->order->billing_last_name, ' '),
            'amount' => $this->source->is_recurring ? $this->source->recurring_amount : $this->source->total, // TEMPORARY FIX FOR TONY ROBBINS
            'currency' => currency($this->source->order->currency),
            'location' => app('iso3166')->expandAbbr(implode(', ', array_filter([$this->source->order->billingstate, $this->source->order->billingcountry])), true) ?: null,
            'date' => $this->source->order->ordered_at ?? $this->source->order->confirmationdatetime,
            'comment' => $this->source->order->is_anonymous ? null : $this->source->order->comments,
            'anonymous' => (bool) $this->source->order->is_anonymous,
        ];
    }

    protected function initializeFromPledge()
    {
        $this->liquid = [
            'id' => $this->source->hashid,
            'type' => 'pledge',
            'name' => $this->source->is_anonymous ? null : $this->source->account->display_name,
            'amount' => $this->source->total_amount,
            'currency' => currency($this->source->currency_code),
            'location' => app('iso3166')->expandAbbr(implode(', ', array_filter([$this->source->account->bill_state, $this->source->account->bill_country])), true) ?: null,
            'date' => $this->source->created_at,
            'comment' => $this->source->is_anonymous ? null : $this->source->comments,
            'anonymous' => (bool) $this->source->is_anonymous,
        ];
    }

    public function initials(): ?string
    {
        return $this->source->is_anonymous ? null : Str::initials($this->liquid['name']);
    }
}
