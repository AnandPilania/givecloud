<?php

namespace Ds\Common\Chargebee\Plans;

class LitePlan extends AbstractPlan
{
    public string $name = 'Lite';

    public string $tier = 'lite';

    public function description(): string
    {
        return 'Self-serve fundraising with full access to how to articles and videos';
    }

    public function features(): array
    {
        return [
            '2.50% Platform Fee',
            'Full access to knowledge base',
            '1 supported integration',
            '10 + fundraising tools',
        ];
    }
}
