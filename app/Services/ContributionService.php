<?php

namespace Ds\Services;

use Ds\Enums\ProductType;
use Ds\Models\Contribution;
use Ds\Models\Order;
use Ds\Models\Payment;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class ContributionService
{
    public function createOrUpdateFromOrder(Order $order): Contribution
    {
        $contribution = $order->contribution ?? new Contribution;

        $contribution->contribution_date = fromUtc($order->ordered_at ?? $order->createddatetime);
        $contribution->total = $order->totalamount;
        $contribution->total_refunded = $order->refunded_amt ?? 0;
        $contribution->currency_code = $order->currency_code;
        $contribution->functional_currency_code = $order->functional_currency_code;
        $contribution->functional_exchange_rate = $order->functional_exchange_rate;
        $contribution->functional_total = $order->functional_total;
        $contribution->is_pos = $order->is_pos;
        $contribution->is_test = $order->is_test;
        $contribution->is_spam = (bool) $order->is_spam;
        $contribution->is_refunded = (bool) $order->refunded_amt;
        $contribution->is_fulfilled = $order->iscomplete;
        $contribution->supporter_id = $order->member_id;
        $contribution->billing_country = $order->billingcountry;
        $contribution->source = $order->source;
        $contribution->recurring_items = $order->recurring_items;

        $order->load('latestPayment');

        $this->setContributionPayment($contribution, $order->latestPayment);
        $this->setContributionOrderItems($contribution, $order->items);

        $contribution->initiated_by = 'customer';
        $contribution->is_recurring = false;
        $contribution->ip_country_matches = $order->ip_country ? $order->ip_country === $order->billingcountry : true;
        $contribution->is_dpo_synced = ($order->alt_contact_id || $order->alt_transaction_id);
        $contribution->dpo_auto_sync = $order->dp_sync_order;
        $contribution->referral_source = $order->referral_source;
        $contribution->tracking_source = $order->tracking_source;
        $contribution->tracking_medium = $order->tracking_medium;
        $contribution->tracking_campaign = $order->tracking_campaign;
        $contribution->tracking_term = $order->tracking_term;
        $contribution->tracking_content = $order->tracking_content;

        $contribution->searchable_text = collect([
            $order->invoicenumber,
            $order->billing_first_name,
            $order->billing_last_name,
            $order->billing_organization_name,
            $order->billingemail,
            $order->shipping_first_name,
            $order->shipping_last_name,
            $order->shipemail,
        ])->filter()->implode('|');

        $contribution->created_at = $order->created_at;
        $contribution->created_by = $order->created_by;
        $contribution->updated_at = $order->updated_at;
        $contribution->updated_by = $order->updated_by;
        $contribution->deleted_at = $order->deleted_at;
        $contribution->deleted_by = $order->deleted_by;
        $contribution->save();

        $order->contribution_id = $contribution->id;
        $order->saveQuietly();

        $order->load('contribution');

        return $contribution;
    }

    public function createOrUpdateFromPayment(Payment $payment): Contribution
    {
        if ($payment->orders->isNotEmpty()) {
            return $this->createOrUpdateFromOrder($payment->orders[0]);
        }

        if ($payment->transactions->isEmpty()) {
            throw new InvalidArgumentException;
        }

        $payment->load('transactions');

        $contribution = $payment->transactions[0]->contribution ?? new Contribution;
        $originalOrder = $payment->transactions[0]->recurringPaymentProfile->order;

        $contribution->contribution_date = fromUtc($payment->created_at);
        $contribution->total = $payment->amount;
        $contribution->total_refunded = $payment->amount_refunded;
        $contribution->currency_code = $payment->currency;
        $contribution->functional_currency_code = $payment->functional_currency_code;
        $contribution->functional_exchange_rate = $payment->functional_exchange_rate;
        $contribution->functional_total = $payment->functional_total;
        $contribution->is_pos = false;
        $contribution->is_test = ! $payment->livemode;
        $contribution->is_spam = $payment->spam;
        $contribution->is_refunded = $payment->refunded;
        $contribution->is_fulfilled = true;
        $contribution->supporter_id = $payment->source_account_id;
        $contribution->billing_country = $originalOrder->billingcountry ?? null;
        $contribution->source = $originalOrder->source ?? null;
        $contribution->recurring_items = 0;

        $this->setContributionPayment($contribution, $payment);
        $this->setContributionOrderItems($contribution, $payment->transactions->pluck('recurringPaymentProfile.order_item')->filter());

        $contribution->initiated_by = 'merchant';
        $contribution->is_recurring = true;
        $contribution->ip_country_matches = $payment->ip_country ? $payment->ip_country === $originalOrder->billingcountry : true;
        $contribution->is_dpo_synced = $payment->transactions->where('dpo_gift_id')->isNotEmpty();
        $contribution->dpo_auto_sync = $payment->transactions->filter->dp_auto_sync->isNotEmpty();
        $contribution->referral_source = $originalOrder->referral_source ?? null;
        $contribution->tracking_source = $originalOrder->tracking_source ?? null;
        $contribution->tracking_medium = $originalOrder->tracking_medium ?? null;
        $contribution->tracking_campaign = $originalOrder->tracking_campaign ?? null;
        $contribution->tracking_term = $originalOrder->tracking_term ?? null;
        $contribution->tracking_content = $originalOrder->tracking_content ?? null;

        $contribution->searchable_text = collect([
            $originalOrder->invoicenumber ?? null,
            $originalOrder->billing_first_name ?? null,
            $originalOrder->billing_last_name ?? null,
            $originalOrder->billing_organization_name ?? null,
            $originalOrder->billingemail ?? null,
            $originalOrder->shipping_first_name ?? null,
            $originalOrder->shipping_last_name ?? null,
            $originalOrder->shipemail ?? null,
        ])->filter()->implode('|');

        $contribution->created_at = $payment->created_at;
        $contribution->created_by = $payment->created_by;
        $contribution->updated_at = $payment->updated_at;
        $contribution->updated_by = $payment->updated_by;
        $contribution->deleted_at = null;
        $contribution->deleted_by = null;
        $contribution->save();

        $payment->transactions()->update(['contribution_id' => $contribution->id]);

        return $contribution;
    }

    private function setContributionPayment(Contribution $contribution, ?Payment $payment): void
    {
        $contribution->payment_id = $payment->id ?? null;
        $contribution->payment_type = $payment->type ?? null;
        $contribution->payment_status = $payment->status ?? null;
        $contribution->payment_reference_number = $payment->reference_number ?? null;
        $contribution->payment_gateway = $payment->gateway ?? null;
        $contribution->payment_card_brand = $payment->card_brand ?? null;
        $contribution->payment_card_last4 = $payment->card_last4 ?? null;
        $contribution->payment_card_cvc_check = $payment->card_cvc_check ?? null;
        $contribution->payment_card_address_line1_check = $payment->card_address_line1_check ?? null;
        $contribution->payment_card_address_zip_check = $payment->card_address_zip_check ?? null;
        $contribution->payment_card_wallet = $payment->card_wallet ?? null;
    }

    private function setContributionOrderItems(Contribution $contribution, Collection $items)
    {
        $contribution->downloadable_items = collect($items)
            ->filter(fn ($item) => $item->variant->file ?? false)
            ->count();

        $contribution->fundraising_items = collect($items)
            ->filter(fn ($item) => ProductType::DONATION_FORM === ($item->variant->product->type ?? null))
            ->count();

        $contribution->membership_items = collect($items)
            ->filter(fn ($item) => isset($item->variant->membership))
            ->count();

        $contribution->shippable_items = collect($items)
            ->filter(fn ($item) => $item->requires_shipping)
            ->count();

        $contribution->sponsorship_items = collect($items)
            ->filter(fn ($item) => (bool) $item->sponsorship_id)
            ->count();
    }
}
