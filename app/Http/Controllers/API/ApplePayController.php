<?php

namespace Ds\Http\Controllers\API;

use Ds\Domain\Commerce\Contracts\ApplePayMerchantDomains;
use Ds\Domain\Commerce\Models\PaymentProvider;

class ApplePayController extends Controller
{
    /**
     * Register controller middleware.
     */
    protected function registerMiddleware()
    {
        //
    }

    /**
     * Verify domain with Apple Pay.
     *
     * @see https://developer.apple.com/documentation/apple_pay_on_the_web/maintaining_your_environment
     * @see https://stripe.com/docs/stripe-js/elements/payment-request-button#verifying-your-domain-with-apple-pay
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        $provider = PaymentProvider::getWalletPayProvider();

        if (! $provider->gateway instanceof ApplePayMerchantDomains) {
            abort(404);
        }

        return response($provider->getAppleDeveloperMerchantIdDomainAssociationFile(), 200, [
            'Content-Type' => 'text/plain',
        ]);
    }
}
