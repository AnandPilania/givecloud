<?php

namespace Ds\Http\Controllers\Frontend;

use Ds\Models\Product;
use Ds\Repositories\DonationFormConfigRepository;

class DonationFormsController extends Controller
{
    protected function registerMiddleware(): void
    {
        $this->middleware('requires.feature:fundraising_forms');
    }

    public function __invoke(DonationFormConfigRepository $donationFormConfigRepo, string $code)
    {
        $product = Product::donationForms()
            ->where('code', $code)
            ->first();

        if (empty($product)) {
            return response($this->renderTemplate('404'), 404);
        }

        $cssFiles = [
            app_asset_url('donation-forms/css/vendor.css'),
            app_asset_url('donation-forms/css/app.css'),
        ];

        $scriptFiles = [
            sprintf('https://maps.googleapis.com/maps/api/js?key=%s&libraries=places', config('services.google-maps.api_key')),
            app_asset_url('donation-forms/js/vendor.js'),
            app_asset_url('donation-forms/js/app.js'),
        ];

        return $this->renderTemplate('~donation-form', [
            'config' => $donationFormConfigRepo->setProduct($product)->getConfig(false),
            'context' => "fundraising-form:{$product->code}",
            'page_title' => $product->name,
            'css_files' => $cssFiles,
            'script_files' => $scriptFiles,
            'show_branding' => false,
            'hide_admin_action_panel' => true,
            'exclude_custom_head_tags' => true,
            'exclude_custom_scripts' => true,
        ]);
    }
}
