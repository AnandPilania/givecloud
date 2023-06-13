<?php

namespace Ds\Http\Controllers\Frontend;

use Ds\Domain\Commerce\Contracts\Viewable;
use Ds\Models\Product;
use Ds\Repositories\DonationFormConfigRepository;
use Ds\Services\GivecloudCoreConfigRepository;
use Illuminate\Http\JsonResponse;

class WidgetsController extends Controller
{
    /**
     * Register controller middleware.
     */
    protected function registerMiddleware()
    {
        $this->middleware('requires.feature:fundraising_forms');
    }

    public function get(GivecloudCoreConfigRepository $coreRepo, string $widgetId): JsonResponse
    {
        $product = Product::donationForms()
            ->where('code', $widgetId)
            ->first();

        $product ??= Product::donationForms()
            ->whereDefaultDonationForm()
            ->firstOrFail();

        $config = $coreRepo->getConfig(['context' => "fundraising-form:{$product->code}"]);

        return response()->json([
            'config' => app(DonationFormConfigRepository::class)->setProduct($product)->getConfig(true),
            'givecloud' => [
                'config' => $config,
                'gateways' => $this->getGivecloudGateways($coreRepo),
            ],
            'scripts' => [
                ['charset' => 'utf-8', 'src' => sprintf('https://maps.googleapis.com/maps/api/js?key=%s&libraries=places', config('services.google-maps.api_key'))],
                ['charset' => 'utf-8', 'src' => secure_site_url(app_asset_url('donation-forms/js/vendor.js'))],
                ['charset' => 'utf-8', 'src' => secure_site_url(app_asset_url('donation-forms/js/app.js'))],
            ],
            'styles' => [
                secure_site_url(app_asset_url('donation-forms/css/vendor.css')),
                secure_site_url(app_asset_url('donation-forms/css/app.css')),
            ],
        ]);
    }

    private function getGivecloudGateways(GivecloudCoreConfigRepository $coreRepo): array
    {
        return collect($coreRepo->getGateways())
            ->filter()
            ->unique('provider')
            ->filter(fn ($provider) => $provider->gateway instanceof Viewable)
            ->map(fn ($provider) => $provider->getViewConfig())
            ->values()
            ->all();
    }
}
