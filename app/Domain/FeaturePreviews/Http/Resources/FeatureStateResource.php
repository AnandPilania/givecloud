<?php

namespace Ds\Domain\FeaturePreviews\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Ds\Domain\FeaturePreviews\PreviewCards\AbstractPreviewCard */
class FeatureStateResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'description' => $this->description(),
            'enabled' => $this->isEnabled(),
            'key' => $this->unprefixedKey(),
            'links' => $this->links(),
            'name' => $this->key(),
            'title' => $this->title(),
        ];
    }
}
