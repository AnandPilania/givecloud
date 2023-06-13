<?php

namespace Database\Stories;

use Database\Factories\ProductFactory;
use Ds\Eloquent\Story;
use Ds\Enums\RecurringFrequency;
use Ds\Models\Product;
use Ds\Models\Variant;

/**
 * @method \Ds\Models\Product|array<Product> create()
 */
class ProductStory extends Story
{
    /** @var \Database\Factories\ProductFactory */
    protected $productFactory;

    /** @var string */
    protected $setupForDonations = null;

    /** @var int|null */
    protected $setupForDpMembership = null;

    public function __construct(ProductFactory $productFactory = null)
    {
        $this->productFactory = $productFactory ?? Product::factory();
    }

    public function setupForDonations(string $recurringFrequency = 'onetime'): self
    {
        $this->setupForDonations = $recurringFrequency;

        return $this;
    }

    public function setupForRecurringDonations(string $recurringFrequency = RecurringFrequency::MONTHLY): self
    {
        return $this->setupForDonations($recurringFrequency);
    }

    public function setupForDpMembership(int $dpId = null): self
    {
        $this->setupForDpMembership = $dpId;

        return $this;
    }

    protected function execute(): Product
    {
        $productFactory = $this->productFactory;
        $variantFactory = Variant::factory()->state(['isdefault' => true]);

        if ($this->setupForDonations) {
            $productFactory = $productFactory
                ->allowOutOfStock()
                ->donation();

            $variantFactory = $variantFactory
                ->donation()
                ->state(['billing_period' => $this->setupForDonations]);
        }

        if ($this->setupForDpMembership) {
            $variantFactory = $variantFactory->dpMembership($this->setupForDpMembership);
        }

        return $productFactory->has($variantFactory, 'variants')->create();
    }
}
