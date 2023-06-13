<?php

namespace Ds\Models\Observers;

use Ds\Models\TaxReceipt;

class TaxReceiptObserver
{
    /**
     * Response to the created event.
     *
     * @param \Ds\Models\TaxReceipt $model
     * @return void
     */
    public function created(TaxReceipt $model)
    {
        // make sure the number is set and save it
        $model->number = $model->formatNumber();
        $model->save();
    }
}
