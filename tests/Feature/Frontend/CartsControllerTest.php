<?php

namespace Tests\Feature\Frontend;

use Tests\TestCase;

class CartsControllerTest extends TestCase
{
    public function testSwitchingToASupportedCurrency()
    {
        sys_set('local_currencies', ['GBP']);

        $this->get(route('frontend.carts.switch_currency', ['GBP']))
            ->assertSessionMissing('liquid_req.error');
    }

    public function testSwitchingToAnUnsupportedCurrency()
    {
        $this->get(route('frontend.carts.switch_currency', ['GCLD_DOLLARS']))
            ->assertSessionHas('liquid_req.error', 'Unsupported currency.');
    }
}
