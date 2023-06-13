<?php

namespace Database\Stories;

use Database\Factories\FundraisingPageFactory;
use Ds\Eloquent\Story;
use Ds\Models\FundraisingPage;
use Ds\Models\Product;

/**
 * @method \Ds\Models\FundraisingPage|array<FundraisingPage> create()
 */
class FundraisingPageStory extends Story
{
    /** @var \Database\Factories\FundraisingPageFactory */
    protected $fundraisingPageFactory;

    /** @var \Ds\Models\Product */
    protected $product;

    public function __construct(FundraisingPageFactory $fundraisingPageFactory = null)
    {
        $this->fundraisingPageFactory = $fundraisingPageFactory ?? FundraisingPage::factory();
    }

    /**
     * @return static
     */
    public function forProductInstance(Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    protected function execute(): FundraisingPage
    {
        if ($this->product) {
            $this->fundraisingPageFactory = $this->fundraisingPageFactory->for($this->product, 'product');
        }

        return $this->fundraisingPageFactory->create();
    }
}
