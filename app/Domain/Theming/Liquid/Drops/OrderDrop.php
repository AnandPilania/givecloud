<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Illuminate\Support\Collection;

class OrderDrop extends CheckoutDrop
{
    /**
     * @param \Ds\Models\Order $source
     */
    protected function initialize($source)
    {
        parent::initialize($source);

        $this->liquid += [
            'created_at' => $source->confirmationdatetime,
            'ordered_at' => $source->ordered_at,
            'comments' => $source->comments,
            'number' => $source->invoicenumber,
            'is_refunded' => $source->is_refunded,
            'is_partially_refunded' => $source->is_partially_refunded,
            'refunded_amt' => $source->refunded_amt,
            'refunded_at' => $source->refunded_at,
            'balance_amt' => $source->balance_amt,
            'cover_costs_enabled' => $source->dcc_enabled_by_customer,
            'cover_costs_amount' => $source->dcc_total_amount,
            'customer_notes' => $source->customer_notes,
        ];
    }

    public function successfulRefunds(): Collection
    {
        return data_get($this->source, 'successfulPayments.0.successfulRefunds');
    }
}
