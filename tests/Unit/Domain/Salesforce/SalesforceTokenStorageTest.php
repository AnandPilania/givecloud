<?php

namespace Tests\Unit\Domain\Salesforce;

use Ds\Domain\Salesforce\SalesforceTokenStorage;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

/**
 * @group salesforce
 */
class SalesforceTokenStorageTest extends TestCase
{
    public function testGetReturnsSetValueFromConfig(): void
    {
        $path = Config::get('forrest.storage.path');
        sys_set($path . 'some_key', 'some_value');

        $this->assertSame('some_value', $this->app->make(SalesforceTokenStorage::class)->get('some_key'));
    }

    public function testPutReturnsGetValue(): void
    {
        $path = Config::get('forrest.storage.path');
        $this->app->make(SalesforceTokenStorage::class)->put('some_key', 'some_value');

        $this->assertSame('some_value', sys_get($path . 'some_key'));
    }

    public function testForgetCallsUnderUnderlyingService(): void
    {
        $service = $this->app->make(SalesforceTokenStorage::class);
        $service->put('some_key', 'some_value');
        $this->assertSame('some_value', $service->get('some_key'));

        $service->forget('some_key');
        $this->assertNull($service->get('some_key'));
    }

    public function testHasReturnsTrueWhenValue(): void
    {
        $service = $this->app->make(SalesforceTokenStorage::class);
        $service->put('some_key', 'some_value');

        $this->assertTrue($service->has('some_key'));
    }

    public function testHasReturnsFalseWhenNoValue(): void
    {
        $service = $this->app->make(SalesforceTokenStorage::class);
        $this->assertFalse($service->has('some_key'));
    }
}
