<?php

namespace Tests\Unit\Domain\HotGlue;

use Ds\Domain\HotGlue\HotGlueClient;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

/**
 * @group hotglue
 */
class HotGlueClientTest extends TestCase
{
    public function testClientSetApiKeyAndReturnsPendingRequest(): void
    {
        Config::set('services.hotglue.private_key', 'my-api-key');

        $client = $this->app->make(HotGlueClient::class)->client();
        $options = $client->getOptions();

        $this->assertSame('my-api-key', data_get($options, 'headers.x-api-key'));
    }
}
