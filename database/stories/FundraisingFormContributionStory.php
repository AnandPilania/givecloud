<?php

namespace Database\Stories;

use Ds\Models\Order as Contribution;
use Ds\Models\Product;

/**
 * @method \Ds\Models\Order|array<Contribution> create()
 */
class FundraisingFormContributionStory extends OnetimeContributionStory
{
    public function __construct(string $formId)
    {
        parent::__construct();

        $this->formId = $formId;
    }

    protected function execute(): Contribution
    {
        $this->setUpContributionDateTestNow();

        $product = Product::query()
            ->donationForms()
            ->hashid($this->formId)
            ->firstOrFail();

        $this->items[] = [
            'variant_id' => $product->variants->firstWhere('billing_period', 'onetime')->getKey(),
            'amt' => $this->amount->getAmount(),
        ];

        $contribution = $this->makeContribution();

        $this->tearDownContributionDateTestNow();

        return $contribution;
    }
}
