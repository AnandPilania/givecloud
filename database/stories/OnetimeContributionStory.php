<?php

namespace Database\Stories;

use Ds\Models\Order as Contribution;
use Faker\Generator as Faker;

/**
 * @method \Ds\Models\Order|array<Contribution> create()
 */
class OnetimeContributionStory extends ContributionStory
{
    /** @var \Ds\Domain\Commerce\Money */
    protected $amount;

    public function __construct()
    {
        $this->amount = money(app(Faker::class)->randomFloat(2, 0.01, 250));
    }

    public function charging(float $amount = null, ?string $currencyCode = null): self
    {
        $this->amount = money($amount, $currencyCode);

        return $this;
    }

    protected function execute(): Contribution
    {
        $this->setUpContributionDateTestNow();

        $product = ProductStory::factory()
            ->setupForDonations()
            ->when($this->forDpMembership, fn (ProductStory $story, $dpId) => $story->setupForDpMembership($dpId))
            ->create();

        if ($this->fromFundraisingPage) {
            $fundraisingPage = FundraisingPageStory::factory()
                ->forProductInstance($product)
                ->create();
        }

        $this->items[] = [
            'variant_id' => $product->defaultVariant->getKey(),
            'amt' => $this->amount->getAmount(),
            'fundraising_page_id' => $fundraisingPage->id ?? null,
            'fundraising_member_id' => $fundraisingPage->member_organizer_id ?? null,
        ];

        $contribution = $this->makeContribution();

        $this->tearDownContributionDateTestNow();

        return $contribution;
    }
}
