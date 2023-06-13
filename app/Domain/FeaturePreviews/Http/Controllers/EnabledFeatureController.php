<?php

namespace Ds\Domain\FeaturePreviews\Http\Controllers;

use Ds\Domain\FeaturePreviews\FeaturePreviewsService;
use Illuminate\Http\Response;

class EnabledFeatureController
{
    /** @var \Ds\Domain\FeaturePreviews\FeaturePreviewsService */
    protected $featurePreviewsService;

    public function __construct(FeaturePreviewsService $featurePreviewsService)
    {
        $this->featurePreviewsService = $featurePreviewsService;
    }

    public function store(string $feature): Response
    {
        if (! $featureModel = $this->featurePreviewsService->get($feature)) {
            return response('Feature not found', Response::HTTP_NOT_FOUND);
        }

        return response(null, $featureModel->enable() ? Response::HTTP_CREATED : Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function destroy(string $feature): Response
    {
        if (! $featureModel = $this->featurePreviewsService->get($feature)) {
            return response('Feature not found', Response::HTTP_NOT_FOUND);
        }

        return response(null, $featureModel->disable() ? Response::HTTP_NO_CONTENT : Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
