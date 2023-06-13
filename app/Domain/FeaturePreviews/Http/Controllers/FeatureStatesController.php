<?php

namespace Ds\Domain\FeaturePreviews\Http\Controllers;

use Ds\Domain\FeaturePreviews\FeaturePreviewsService;
use Ds\Domain\FeaturePreviews\Http\Resources\FeatureStateResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FeatureStatesController
{
    /** @var \Ds\Domain\FeaturePreviews\FeaturePreviewsService */
    protected $featurePreviewsService;

    public function __construct(FeaturePreviewsService $featurePreviewsService)
    {
        $this->featurePreviewsService = $featurePreviewsService;
    }

    public function index(): AnonymousResourceCollection
    {
        return FeatureStateResource::collection($this->featurePreviewsService->features()->values());
    }
}
