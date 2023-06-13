<?php

namespace Ds\Services;

class DonorCoversCostsService
{
    public function getCost(float $amount, ?string $type = null): float
    {
        return $this->getCosts($amount)[$type] ?? 0.0;
    }

    public function getCosts(float $amount): array
    {
        [$minimumCosts, $moreCosts, $mostCosts] = $this->getCostsForAmount($amount);

        return [
            'minimum_costs' => $minimumCosts,
            'more_costs' => $moreCosts,
            'most_costs' => $mostCosts,
        ];
    }

    private function getCostsForAmount(float $amount): array
    {
        if (! sys_get('dcc_ai_is_enabled')) {
            $costs = round(sys_get('dcc_cost_per_order') + ($amount * sys_get('dcc_percentage')) / 100, 2);

            return [$costs, $costs, $costs];
        }

        $minimalCosts = max(1.56, round($amount * 0.07, 2) + 0.79);
        $moreCosts = $this->normalizeCosts(round($minimalCosts * 1.35, 2));
        $mostCosts = round($moreCosts * 1.35, 2);

        return [$minimalCosts, $moreCosts, $mostCosts];
    }

    private function normalizeCosts(float $costs): float
    {
        if ($costs < 5.9) {
            return $this->normalizeCostsWithinRange($costs, 5, 5.89);
        }

        if ($costs % 10 === 0) {
            return $this->normalizeCostsWithinRange($costs, floor($costs), floor($costs) + 0.49);
        }

        return $costs;
    }

    private function normalizeCostsWithinRange(float $costs, float $min, float $max): float
    {
        if ($costs < $min || $costs > $max) {
            return $costs;
        }

        $baseCostsOffset = 0.10;

        $baseCosts = $min - $baseCostsOffset;
        $squashedCostsInRange = round(($costs - $min) / ($max - $min) * ($baseCostsOffset - 0.01), 2);

        return $baseCosts + $squashedCostsInRange;
    }
}
