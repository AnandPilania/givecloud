<?php

namespace Ds\Domain\FeaturePreviews;

use Ds\Domain\FeaturePreviews\PreviewCards\AbstractPreviewCard;
use Illuminate\Support\Collection;

class FeaturePreviewsService
{
    /** @var \Illuminate\Support\Collection */
    protected $features;

    public function __construct()
    {
        $this->gatherFeatures();
    }

    public function features(): Collection
    {
        return $this->features;
    }

    public function get(string $name): ?AbstractPreviewCard
    {
        if ($this->features->has($name)) {
            return $this->features->get($name);
        }

        return null;
    }

    protected function gatherFeatures(): void
    {
        $this->features = collect(config('feature-previews'))->mapWithKeys(function ($item) {
            /** @var \Ds\Domain\FeaturePreviews\PreviewCards\AbstractPreviewCard $feature */
            $feature = app($item);

            return [$feature->key() => $feature];
        });
    }
}
