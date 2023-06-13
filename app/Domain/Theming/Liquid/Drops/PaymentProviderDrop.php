<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;

class PaymentProviderDrop extends Drop
{
    protected function initialize($source)
    {
        $this->liquid = [
            'name' => $source->display_name,
            'provider' => $source->provider,
            'provider_type' => $source->provider_type,
            'transaction_cost' => $source->transaction_cost,
            'transaction_rate' => $source->transaction_rate,
        ];
    }
}
