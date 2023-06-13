<?php

namespace Ds\Common\Chargebee\Plans;

use ChargeBee\ChargeBee\Models\Plan as ChargeBeePlan;
use Ds\Common\Chargebee\BillingPlansService;
use Ds\Repositories\ChargebeeRepository;

abstract class AbstractPlan
{
    public string $tier = '';

    public string $name;

    public bool $most_popular = false;

    public float $transactionFees = 0.025;

    public string $support = 'priority_3';

    abstract public function features(): array;

    abstract public function description(): string;

    public function asChargebeeAnnualPlan(): ?ChargeBeePlan
    {
        return $this->asChargebeePlan(false);
    }

    public function asChargebeeMonthlyPlan(): ?ChargeBeePlan
    {
        return $this->asChargebeePlan();
    }

    public function asChargebeePlan(bool $monthly = true): ?ChargeBeePlan
    {
        $chargebeeId = $this->chargebeeIdForTier($monthly);

        return app(ChargebeeRepository::class)->getPlans()->firstWhere('id', $chargebeeId);
    }

    public function chargebeeIdForTier(bool $monthly = true): ?string
    {
        $key = $monthly ? 'monthly' : 'annually';

        return data_get(app(BillingPlansService::class)->fromConfig(), $key . '.' . $this->tier);
    }

    public function checkoutLink(bool $monthly = true): string
    {
        if (empty($this->tier)) {
            return '';
        }

        return sprintf(
            "javascript:j.createChargeBeeCheckout('%s')",
            $monthly ? $this->asChargebeeMonthlyPlan()->id : $this->asChargebeeAnnualPlan()->id
        );
    }

    public function hasPrice(): bool
    {
        return $this->asChargebeeAnnualPlan() || $this->asChargebeeMonthlyPlan();
    }

    public function missionControlPlanName()
    {
        return config('services.missioncontrol.plans.' . $this->tier);
    }
}
