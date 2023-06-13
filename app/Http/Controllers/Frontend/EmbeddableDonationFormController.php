<?php

namespace Ds\Http\Controllers\Frontend;

class EmbeddableDonationFormController extends Controller
{
    public function index($code)
    {
        // get the product
        $productModel = \Ds\Models\Product::whereCode(urldecode($code))
            ->with('variants', 'defaultVariant', 'customFields', 'memberships')
            ->first();

        // was there an error?
        if ($productModel === false) {
            return response($this->renderTemplate('404'), 404);
        }

        $cssFiles = [
            app_asset_url('embeddable-form/donate/css/app.css'),
            jpanel_asset_url('dist/css/tailwind.css'),
        ];

        $script_Files = [
            app_asset_url('embeddable-form/donate/js/vendor.js'),
            app_asset_url('embeddable-form/donate/js/app.js'),
        ];

        $donation_based_variants = collect($productModel->variants)->filter(function ($variant) {
            return $variant->is_donation;
        });

        $variantUnitAmounts = [];
        $disclaimertext = '';

        return $this->renderTemplate('~embeddable/product', [
            'context' => "embeddable.donation:{$productModel->code}",
            'product' => $productModel,
            'disclaimertext' => $disclaimertext,
            'account_types' => \Ds\Models\AccountType::onWeb()->get(),
            'css_files' => $cssFiles,
            'script_files' => $script_Files,
            'has_donation_variants' => count($donation_based_variants) > 0 ? true : false,
            'variantUnitAmounts' => $variantUnitAmounts,
            'show_branding' => false,
            'hide_admin_action_panel' => true,
            'exclude_custom_scripts' => true,
        ]);
    }
}
