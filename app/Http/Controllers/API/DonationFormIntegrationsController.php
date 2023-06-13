<?php

namespace Ds\Http\Controllers\API;

use Ds\Http\Resources\DonationForms\DonationFormResource;
use Ds\Models\Product;
use Illuminate\Http\Request;

class DonationFormIntegrationsController extends Controller
{
    public function update(string $donationForm, Request $request): DonationFormResource
    {
        /** @var \Ds\Models\Product $product */
        $product = Product::query()
            ->donationForms()
            ->hashid($donationForm)
            ->firstOrFail();

        $product->forceFill([
            'meta1' => $request->input('dp_gl_code'),
            'meta2' => $request->input('dp_campaign'),
            'meta3' => $request->input('dp_solicit_code'),
            'meta4' => $request->input('dp_sub_solicit_code'),

            // save DP user-defined fields
            'meta9' => $request->input('dp_meta_9'),
            'meta10' => $request->input('dp_meta_10'),
            'meta11' => $request->input('dp_meta_11'),
            'meta12' => $request->input('dp_meta_12'),
            'meta13' => $request->input('dp_meta_13'),
            'meta14' => $request->input('dp_meta_14'),
            'meta15' => $request->input('dp_meta_15'),
            'meta16' => $request->input('dp_meta_16'),
            'meta17' => $request->input('dp_meta_17'),
            'meta18' => $request->input('dp_meta_18'),
            'meta19' => $request->input('dp_meta_19'),
            'meta20' => $request->input('dp_meta_20'),
            'meta21' => $request->input('dp_meta_21'),
            'meta22' => $request->input('dp_meta_22'),
        ]);

        $product->metadata([
            'donation_forms_dp_autosync_enabled' => (bool) $request->input('dp_enabled'),
            'donation_forms_gtm_container_id' => $request->input('gtm_container_id'),
            'donation_forms_google_ads_pixel_id' => $request->input('google_ads_pixel_id'),
            'donation_forms_meta_pixel_id' => $request->input('meta_pixel_id'),
        ]);

        $product->save();

        return DonationFormResource::make($product);
    }
}
