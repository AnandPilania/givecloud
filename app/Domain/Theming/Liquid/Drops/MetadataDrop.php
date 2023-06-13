<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;
use Ds\Models\PledgeCampaign;
use Ds\Models\Product;
use Ds\Models\Variant;

class MetadataDrop extends Drop
{
    /**
     * Liquid representation of model.
     *
     * @return mixed
     */
    public function toLiquid()
    {
        switch ($this->source->type) {
            case 'oembed':
                return nullable_cast('array', oembed_get($this->source->value));
            case 'pledge-campaigns':
                $campaigns = PledgeCampaign::whereIn('id', explode(',', $this->source->value))->get();

                return Drop::collectionFactory($campaigns, 'PledgeCampaign');
            case 'pledge-campaign':
                return Drop::factory(PledgeCampaign::find($this->source->value), 'PledgeCampaign');
            case 'products':
                $ids = explode(',', $this->source->value);

                return Drop::collectionFactory(Product::find($ids)->sortByValues($ids), 'Product');
            case 'product':
                return Drop::factory(Product::find($this->source->value), 'Product');
            case 'selectize-tags':
                return $this->source->getValue('csv');
            case 'variants':
                $ids = explode(',', $this->source->value);

                return Drop::collectionFactory(Variant::find($ids)->sortByValues($ids), 'Variant', ['includeProduct' => true]);
            case 'variant':
                return Drop::factory(Variant::find($this->source->value), 'Variant', ['includeProduct' => true]);
        }

        return $this->source->value;
    }
}
