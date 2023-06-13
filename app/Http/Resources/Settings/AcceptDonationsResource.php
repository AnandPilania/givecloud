<?php

namespace Ds\Http\Resources\Settings;

use Ds\Domain\Commerce\PaymentProviderService;
use Ds\Illuminate\Http\Resources\Json\JsonResource;

class AcceptDonationsResource extends JsonResource
{
    public function __construct()
    {
        $this->resource = new \stdClass();
    }

    public function toArray($request): array
    {
        $stripe = app(PaymentProviderService::class)->firstOrCreate('stripe');

        return [
            'stripe' => [
                'is_enabled' => (bool) data_get($stripe, 'enabled'),
                'is_ach_allowed' => (bool) data_get($stripe, 'is_ach_allowed'),
                'is_multicurrency_supported' => (bool) sys_get('local_currencies'),
                'is_wallet_pay_allowed' => (bool) data_get($stripe, 'is_wallet_pay_allowed'),
                'connect_url' => rescueQuietly(fn () => optional($stripe)->getAuthenticationUrl()),
            ],
            'paypal' => [
                'is_enabled' => false,
            ],
            'venmo' => [
                'is_enabled' => false,
            ],
        ];
    }
}
