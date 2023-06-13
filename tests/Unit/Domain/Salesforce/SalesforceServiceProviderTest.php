<?php

namespace Tests\Unit\Domain\Salesforce;

use Omniphx\Forrest\Authentications\WebServer;
use Tests\TestCase;

/**
 * @group salesforce
 */
class SalesforceServiceProviderTest extends TestCase
{
    public function testResolvingServiceSetsCredentials(): void
    {
        $mock = $this->mock(WebServer::class);
        $mock->shouldReceive('setCredentials')->once();

        $this->app->bind('forrest', function () use ($mock) {
            return $mock;
        });

        $this->app->make('forrest');
    }
}
