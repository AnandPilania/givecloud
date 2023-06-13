<?php

namespace Tests\Unit\Domain\Commerce\Support;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TaxCloudTest extends TestCase
{
    public function testHandlingAuthenticationFailure(): void
    {
        Http::fake([
            'taxcloud.net/*' => Http::fixture('taxcloud/authentication-failure.json'),
        ]);

        $this->expectExceptionMessage('Error calculating tax. Invalid apiLoginID and/or apiKey');

        app('taxCloud')->ping();
    }

    public function testPinging(): void
    {
        Http::fake([
            'taxcloud.net/*' => Http::fixture('taxcloud/ping.json'),
        ]);

        $this->assertSame(3, app('taxCloud')->ping()->ResponseType);
    }
}
