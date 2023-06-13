<?php

namespace Ds\Models\Observers;

use Ds\Models\OrderItem;

class OrderItemObserver
{
    /**
     * Response to the saving event.
     *
     * @param \Ds\Models\OrderItem $model
     * @return void
     */
    public function saving(OrderItem $model)
    {
        if (! $model->order->is_paid) {
            $model->dcc_eligible = $model->is_eligible_for_dcc;
        }
    }

    /**
     * Response to the saved event.
     *
     * @param \Ds\Models\OrderItem $model
     * @return void
     */
    public function saved(OrderItem $model)
    {
        // any time an order item is changed
        // and the item has NOT been paid for
        if (! $model->order->is_paid) {
            // re-apply promo codes
            $model->order->reapplyPromos();

            // make sure the number is set and save it
            $model->order->calculate();
        }
    }

    /**
     * Response to the deleting event.
     *
     * @param \Ds\Models\OrderItem $model
     * @return void
     */
    public function deleting(OrderItem $model)
    {
        // make sure we kill all taxes
        $model->unapplyTaxes();
    }

    /**
     * Response to the deleted event.
     *
     * @param \Ds\Models\OrderItem $model
     * @return void
     */
    public function deleted(OrderItem $model)
    {
        // make sure the number is set and save it
        $model->order->calculate();
    }
}
