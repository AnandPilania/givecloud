<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;
use Ds\Enums\RecurringPaymentProfileStatus;
use Ds\Services\DonorCoversCostsService;
use Ds\Services\RecurringPaymentProfileService;

class SubscriptionDrop extends Drop
{
    /**
     * @param \Ds\Models\RecurringPaymentProfile $source
     */
    protected function initialize($source)
    {
        $this->liquid = [
            'id' => $source->profile_id,
            'status' => strtolower($source->status),
            'locked' => $source->is_locked,
            'start_date' => $source->profile_start_date,
            'next_payment_date' => $source->next_billing_date,
            'next_possible_billing_date' => app(RecurringPaymentProfileService::class)->getNextPossibleBillingDate($source, fromDate($source->last_payment_date)),
            'amount' => $source->amt,
            'total_amount' => $source->total_amt,
            'currency' => new CurrencyDrop(currency($source->currency_code)),
            'billing_period' => $source->billing_period,
            'billing_frequency' => $source->billing_frequency,
            'billing_cycle_anchor' => $source->billing_cycle_anchor,
            'description' => $source->description,
            'amount_collected' => $source->aggregate_amount,
            'cover_costs_enabled' => $source->dcc_enabled_by_customer,
            'cover_costs_amount' => $source->dcc_enabled_by_customer ? $source->dcc_amount : 0,
            'cover_costs_cost_per_order' => $source->dcc_enabled_by_customer ? $source->dcc_per_order_amount : (float) sys_get('dcc_cost_per_order'),
            'cover_costs_percentage' => $source->dcc_enabled_by_customer ? $source->dcc_rate : (float) sys_get('dcc_percentage'),
            'cover_costs_type' => $source->dcc_type,
            'has_legacy_cover_costs' => $source->dcc_amount > 0 && ! in_array($source->dcc_amount, app(DonorCoversCostsService::class)->getCosts($source->amt)),
        ];
    }

    public function ended()
    {
        return in_array($this->source->status, [
            RecurringPaymentProfileStatus::EXPIRED,
            RecurringPaymentProfileStatus::CANCELLED,
        ]);
    }

    public function gl_code()
    {
        return $this->source->gl_code;
    }

    public function payment_method()
    {
        return Drop::factory($this->source->paymentMethod, 'PaymentMethod', [
            'subscriptions' => [],
        ]);
    }

    public function payments()
    {
        $this->source->loadMissing('payments.refunds');

        return $this->source->payments->sortByDesc('created_at');
    }

    public function feature_image()
    {
        if ($this->source->variant) {
            return $this->source->variant->media()->images()->first() ?? $this->source->variant->product->photo;
        }

        return $this->source->sponsorship->featuredImage ?? null;
    }

    public function can_cover_costs()
    {
        if ($this->source->dcc_enabled_by_customer) {
            return true;
        }

        return $this->source->order_item->is_eligible_for_dcc ?? false;
    }

    public function billing_period_abbreviation()
    {
        switch ($this->source->billing_period) {
            case 'Day':       return trans('payments.period.daily');
            case 'Week':      return trans('payments.period.weekly');
            case 'SemiMonth': return trans('payments.period.semi_monthly');
            case 'Month':     return trans('payments.period.monthly');
            case 'Quarter':   return trans('payments.period.quarterly');
            case 'SemiYear':  return trans('payments.period.semi_yearly');
            case 'Year':      return trans('payments.period.yearly');
            default:          return '';
        }
    }
}
