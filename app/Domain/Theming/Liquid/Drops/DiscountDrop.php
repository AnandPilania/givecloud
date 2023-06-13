<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;
use Ds\Models\Order;
use Ds\Models\OrderItem;
use Ds\Models\PromoCode;

class DiscountDrop extends Drop
{
    /** @var \Ds\Models\Order */
    protected $order;

    /** @var \Ds\Models\OrderItem */
    protected $item;

    /**
     * Create an instance.
     *
     * @param \Ds\Models\PromoCode $source
     * @param \Ds\Models\Order $order
     */
    public function __construct(PromoCode $source, Order $order, OrderItem $item = null)
    {
        $this->order = $order;
        $this->item = $item;

        parent::__construct($source);
    }

    protected function initialize($source)
    {
        $this->liquid = [
            'id' => $source->pivot->id ?? null,
            'title' => $source->description ?: null,
            'code' => $source->code,
            'savings' => 0 - $source->discount,
            'free_shipping' => (bool) $this->source->is_free_shipping,
        ];
    }

    public function amount()
    {
        if ($this->item) {
            return ($this->item->variant->price * $this->item->qty) - $this->item->total;
        }

        return 0;
    }

    public function savings()
    {
        return 0 - $this->invokeDrop('amount');
    }

    public function total_amount()
    {
        $amount = 0;

        foreach ($this->order->items->where('promocode', $this->source->code) as $item) {
            $amount += ($item->variant->price * $item->qty) - $item->total;
        }

        return $amount;
    }

    public function total_savings()
    {
        return 0 - $this->invokeDrop('total_amount');
    }

    public function type()
    {
        if ($this->source->is_free_shipping && ! $this->source->discount) {
            return 'ShippingDiscount';
        }

        if ($this->source->discount_type === 'dollar') {
            return 'FixedAmountDiscount';
        }

        return 'PercentageDiscount';
    }
}
