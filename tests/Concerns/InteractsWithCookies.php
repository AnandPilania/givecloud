<?php

namespace Tests\Concerns;

use Laravel\Dusk\Browser;

trait InteractsWithCookies
{
    public function tearDown(): void
    {
        parent::tearDown();

        $this->browse(function (Browser $browser) {
            $browser->driver->manage()->deleteAllCookies();
        });
    }
}
