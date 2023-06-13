<?php

namespace Ds\Models\Observers;

use Ds\Domain\QuickStart\Events\QuickStartTaskAffected;
use Ds\Domain\QuickStart\Tasks\DonationItem;
use Ds\Models\Product;

class ProductObserver
{
    public function saved(Product $model)
    {
        QuickStartTaskAffected::dispatch(DonationItem::initialize());
    }

    public function deleted(Product $model)
    {
        QuickStartTaskAffected::dispatch(DonationItem::initialize());
    }

    public function restored(Product $model)
    {
        QuickStartTaskAffected::dispatch(DonationItem::initialize());
    }

    /**
     * Response to the deleting event.
     *
     * @param \Ds\Models\Product $model
     * @return void
     */
    public function deleting(Product $model)
    {
        $model->is_deleted = true;
        $model->deleted_by = user('id');
    }
}
