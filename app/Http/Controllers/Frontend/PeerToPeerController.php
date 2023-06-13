<?php

namespace Ds\Http\Controllers\Frontend;

use Ds\Models\FundraisingPage;
use Ds\Models\Product;
use Ds\Repositories\DonationFormConfigRepository;
use Ds\Repositories\PeerToPeerConfigRepository;

class PeerToPeerController extends Controller
{
    protected function registerMiddleware(): void
    {
        $this->middleware('requires.feature:fundraising_forms_peer_to_peer');
    }

    public function __invoke()
    {
        $product = Product::donationForms()
            ->whereDefaultDonationForm()
            ->firstOrFail();

        return redirect()->route('peer-to-peer-campaign.spa', ['code' => $product->code]);
    }

    public function getFundraisingForm(string $code)
    {
        $cssFiles = [
            app_asset_url('peer-to-peer/css/vendor.css'),
            app_asset_url('peer-to-peer/css/app.css'),
        ];

        $scriptFiles = [
            app_asset_url('peer-to-peer/js/vendor.js'),
            app_asset_url('peer-to-peer/js/app.js'),
        ];

        $product = Product::donationForms()
            ->where('code', $code)
            ->firstOrFail();

        $peerToPeerConfigRepo = app(PeerToPeerConfigRepository::class)
            ->setProduct($product);

        return $this->renderTemplate('~peer-to-peer', [
            'config' => $peerToPeerConfigRepo->getConfig(),
            'css_files' => $cssFiles,
            'script_files' => $scriptFiles,
            'show_branding' => false,
            'hide_admin_action_panel' => true,
            'exclude_custom_head_tags' => true,
            'exclude_custom_scripts' => true,
        ]);
    }

    public function getDonationForm(string $code)
    {
        $peerToPeer = FundraisingPage::query()
            ->standaloneType()
            ->hashid($code)
            ->firstOrFail();

        $donationFormConfigRepo = app(DonationFormConfigRepository::class)
            ->setFundraisingPage($peerToPeer);

        return app(DonationFormsController::class)(
            $donationFormConfigRepo,
            $peerToPeer->product->hashid,
        );
    }
}
