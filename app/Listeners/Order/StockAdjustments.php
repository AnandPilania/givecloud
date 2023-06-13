<?php

namespace Ds\Listeners\Order;

use Ds\Enums\StockAdjustmentState;
use Ds\Enums\StockAdjustmentType;
use Ds\Events\OrderWasCompleted;
use Ds\Models\StockAdjustment;

class StockAdjustments
{
    /**
     * Handle the event.
     *
     * @param \Ds\Events\OrderWasCompleted $event
     * @return void
     */
    public function handle(OrderWasCompleted $event)
    {
        foreach ($event->order->items as $item) {
            if ($item->variant) {
                $adjustment = new StockAdjustment;
                $adjustment->type = StockAdjustmentType::ADJUSTMENT;
                $adjustment->variant_id = $item->variant->id;
                $adjustment->state = StockAdjustmentState::SOLD;
                $adjustment->quantity = $item->qty;
                $adjustment->occurred_at = $event->order->confirmationdatetime->copy();
                $adjustment->user_id = 1;
                $adjustment->save();
            }
        }
    }
}
