<?php

namespace Ds\Common\Chargebee;

use Ds\Common\Chargebee\Plans\AbstractPlan;
use Ds\Common\Chargebee\Plans\ImpactPlan;
use Ds\Common\Chargebee\Plans\ImpactPlusPlan;
use Ds\Common\Chargebee\Plans\LitePlan;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class BillingPlansService
{
    protected $plans = [
        LitePlan::class,
        ImpactPlan::class,
        ImpactPlusPlan::class,
    ];

    public function all(): Collection
    {
        return collect($this->plans)->map(function (string $plan) {
            return app($plan);
        });
    }

    public function fromChargebeeId(string $id): ?AbstractPlan
    {
        return $this->all()->first(function (AbstractPlan $plan) use ($id) {
            return optional($plan->asChargebeeMonthlyPlan())->id === $id
               || optional($plan->asChargebeeAnnualPlan())->id === $id;
        });
    }

    public function chargebeeIds(): array
    {
        return Arr::flatten($this->fromConfig());
    }

    public function currency(): string
    {
        return in_array(sys_get('dpo_currency'), ['CAD', 'USD'], true)
            ? sys_get('dpo_currency')
            : 'USD';
    }

    public function fromConfig(): Collection
    {
        $enabledPlans = config('services.chargebee.plans');

        $currency = strtolower(sys_get('dpo_currency'));

        return collect(Arr::get($enabledPlans, $currency, $enabledPlans['*']));
    }
}
