<?php

namespace Ds\Domain\Commerce;

use Ds\Domain\Commerce\Models\PaymentProvider;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PaymentProviderService
{
    public function firstOrCreate(string $name, string $type = 'credit'): PaymentProvider
    {
        try {
            return PaymentProvider::provider($name)->firstOrFail();
        } catch (ModelNotFoundException $exception) {
            // do nothing
        }

        $provider = new PaymentProvider;
        $provider->enabled = false;
        $provider->provider = $name;
        $provider->provider_type = $type;
        $provider->test_mode = isDev();

        // test mode needs to be set prior to calling the gateway for the first time so
        // that any code run when instantiating the gateway is run in the correct context, i.e. prod vs test
        // @see https://github.com/givecloud/givecloud/pull/2235
        $provider->display_name = $provider->gateway->getDisplayName();

        if ($type === 'credit') {
            $provider->cards = 'amex,discover,mastercard,visa';
        }

        return $provider;
    }
}
