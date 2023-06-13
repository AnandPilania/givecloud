<?php

namespace Ds\Common\Chargebee\Plans;

class ImpactPlan extends AbstractPlan
{
    public string $name = 'Impact';

    public string $tier = 'impact';

    public string $support = 'priority_1';

    public float $transactionFees = 0.02;

    public bool $most_popular = true;

    public function description(): string
    {
        return 'Expert service and proactive TRUSTRAISING optimization ';
    }

    public function features(): array
    {
        return [
            '2% Platform Fee',
            'All Lite features',
            '2 additional integrations',
            'Reduced platform fee',
            'Multi-site configuration (up to 3)',
            'Priority support response time',
            'Kiosk & mobile POS applications',
            'Sponsorships',
        ];
    }
}
