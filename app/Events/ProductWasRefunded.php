<?php

namespace Ds\Events;

use Ds\Models\OrderItem;
use Ds\Models\Product;
use Illuminate\Queue\SerializesModels;

class ProductWasRefunded extends Event
{
    use SerializesModels;

    /** @var \Ds\Models\Product */
    public $product;

    /** @var \Ds\Models\OrderItem */
    public $item;

    /**
     * Create a new event instance.
     *
     * @param \Ds\Models\Product $product
     * @param \Ds\Models\OrderItem $item
     * @return void
     */
    public function __construct(Product $product, OrderItem $item)
    {
        $this->product = $product;
        $this->item = $item;
    }
}
