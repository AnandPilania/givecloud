<?php

namespace Ds\Models\Observers;

use Ds\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;

class PaymentMethodObserver
{
    /**
     * Response to the saved event.
     *
     * @param \Ds\Models\PaymentMethod $model
     * @return void
     */
    public function saved(PaymentMethod $model)
    {
        $this->updateDefaultPaymentMethod($model);
    }

    /**
     * Response to the deleted event.
     *
     * @param \Ds\Models\PaymentMethod $model
     * @return void
     */
    public function deleted(PaymentMethod $model)
    {
        $this->updateDefaultPaymentMethod($model);
    }

    /**
     * Update the default Payment Method.
     *
     * @return void
     */
    protected function updateDefaultPaymentMethod(PaymentMethod $model)
    {
        // If there is no default payment method then
        // set the first available payment method as the default.
        if ($model->member && ! $model->member->defaultPaymentMethod) {
            // find the first payment method
            $defaultMethod = $model->member->paymentMethods()->active()->first();

            // if a payment method was found
            if ($defaultMethod) {
                // set it to the default
                $defaultMethod->useAsDefaultPaymentMethod();

                // find all recurring payments using the old method and change it
                DB::table('recurring_payment_profiles')
                    ->where('payment_method_id', $model->id)
                    ->update(['payment_method_id' => $defaultMethod->id]);
            }
        }
    }
}
