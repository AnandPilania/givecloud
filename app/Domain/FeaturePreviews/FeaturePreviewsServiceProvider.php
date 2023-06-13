<?php

namespace Ds\Domain\FeaturePreviews;

use Illuminate\Support\ServiceProvider;

class FeaturePreviewsServiceProvider extends ServiceProvider
{
    /**
     * Register the FeaturePreviewsService singleton.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(FeaturePreviewsService::class);
    }
}
