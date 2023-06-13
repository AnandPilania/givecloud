<?php

namespace Ds\Common\Chargebee\Plans;

class ImpactPlusPlan extends AbstractPlan
{
    public string $name = 'Impact Plus';

    public function description(): string
    {
        return 'For our enterprise level subscribers who may need a little more';
    }

    public function features(): array
    {
        return [
            '< 1.25% Platform Fee',
            'All Impact features',
            'Unlimited support integrations',
            'Dedicated account manager',
            'Multi-site configuration (4+)',
            'Personalized onboarding',
        ];
    }
}
