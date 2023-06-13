<?php

namespace Ds\Repositories;

use Carbon\Carbon;
use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Enums\RecurringPaymentProfileStatus;
use Ds\Models\OrderItem;
use Ds\Models\PaymentMethod;
use Ds\Models\RecurringPaymentProfile;

class RecurringPaymentProfileRepository
{
    /**
     * Create new Recurring Payment Profile.
     *
     * @param \Ds\Models\OrderItem $item
     * @param \Ds\Models\PaymentMethod $paymentMethod
     * @return \Ds\Models\RecurringPaymentProfile
     */
    public function createRecurringPaymentProfile(OrderItem $item, PaymentMethod $paymentMethod)
    {
        if (! $item->order->member_id) {
            throw new MessageException('The contribution must be associated with a supporter to create a recurring payment profile.');
        }

        $taxAmount = (float) db_var('SELECT amount FROM productorderitemtax WHERE orderitemid = %d', $item->id);

        $rpp = new RecurringPaymentProfile;
        $rpp->member_id = $item->order->member_id;
        $rpp->status = RecurringPaymentProfileStatus::ACTIVE;
        $rpp->subscriber_name = trim($paymentMethod->billing_first_name . ' ' . $paymentMethod->billing_last_name);
        $rpp->profile_start_date = new Carbon;
        $rpp->profile_reference = $item->order->client_uuid;
        $rpp->aggregate_amount = 0;
        $rpp->description = $item->variant->product->name;
        $rpp->max_failed_payments = sys_get('rpp_retry_attempts');
        $rpp->auto_bill_out_amt = sys_get('rpp_auto_bill_out_amt');
        $rpp->ship_to_name = trim($item->order->shipping_first_name . ' ' . $item->order->shipping_last_name);
        $rpp->ship_to_street = $item->order->shipaddress1;
        $rpp->ship_to_street2 = $item->order->shipaddress2;
        $rpp->ship_to_city = $item->order->shipcity;
        $rpp->ship_to_state = $item->order->shipstate;
        $rpp->ship_to_zip = $item->order->shipzip;
        $rpp->ship_to_country = $item->order->shipcountry;
        $rpp->ship_to_phone_num = $item->order->shipphone;
        $rpp->transaction_type = $item->variant->isdonation ? 'Donation' : 'Standard';
        $rpp->init_amt = $item->recurring_with_initial_charge ? $item->total + $taxAmount : 0.00;
        $rpp->amt = $item->recurring_amount;
        $rpp->tax_amt = $taxAmount;
        $rpp->num_cyles_completed = 0;
        $rpp->last_payment_date = null;
        $rpp->last_payment_amt = 0;
        $rpp->payment_method_id = $paymentMethod->id;
        $rpp->productorder_id = $item->order->id;
        $rpp->productorderitem_id = $item->id;
        $rpp->productinventory_id = $item->variant->id;
        $rpp->product_id = $item->variant->product->id;

        // set the billing period based on the recurring_frequency
        switch ($item->recurring_frequency) {
            case 'daily':     $rpp->billing_period = 'Day'; break;
            case 'weekly':    $rpp->billing_period = 'Week'; break;
            case 'biweekly':  $rpp->billing_period = 'SemiMonth'; break;
            case 'monthly':   $rpp->billing_period = 'Month'; break;
            case 'quarterly': $rpp->billing_period = 'Quarter'; break;
            case 'biannually': $rpp->billing_period = 'SemiYear'; break;
            case 'annually':  $rpp->billing_period = 'Year'; break;
        }

        $rpp->setProfileStartDate($item->recurring_day, $item->recurring_day_of_week);
        $rpp->next_billing_date = $rpp->profile_start_date->copy();

        return $rpp;
    }
}
