<?php

namespace Tests\Unit\Domain\HotGlue;

use Ds\Domain\HotGlue\HotGlue;
use Ds\Domain\HotGlue\Targets\HubSpotTarget;
use Ds\Domain\HotGlue\Targets\MailchimpTarget;
use Ds\Domain\HotGlue\Targets\SalesforceTarget;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ItemNotFoundException;
use Tests\TestCase;

/**
 * @group hotglue
 */
class HotGlueTest extends TestCase
{
    public function testTargetThrowsExceptionWhenNotImplemented(): void
    {
        $this->expectException(ItemNotFoundException::class);

        $this->app->make(HotGlue::class)->target('unknown');
    }

    /** @dataProvider targetInstancesDataProvider */
    public function testTargetReturnsTargetInstance($target, $expectedInstance): void
    {
        $instance = $this->app->make(HotGlue::class)->target($target);

        $this->assertInstanceOf($expectedInstance, $instance);
    }

    public function targetInstancesDataProvider(): array
    {
        return [
            ['salesforce', SalesforceTarget::class],
            ['mailchimp', MailchimpTarget::class],
            ['hubspot', HubSpotTarget::class],
        ];
    }

    public function testConfigReturnsConfig(): void
    {
        Config::set('services.hotglue.api_key', 'my_api_secret');
        Config::set('services.hotglue.env_id', 'my_env_id');
        Config::set('services.hotglue.hubspot.flow_id', 'hs_flow_id');
        Config::set('services.hotglue.hubspot.target_id', 'hubspot-v4');

        $config = $this->app->make(HotGlue::class)->config('hubspot');

        $this->assertIsArray($config);

        $this->assertSame('my_api_secret', data_get($config, 'apiKey'));
        $this->assertSame('my_env_id', data_get($config, 'envId'));
        $this->assertSame('hs_flow_id', data_get($config, 'flowId'));
        $this->assertSame('hubspot', data_get($config, 'target.name'));
        $this->assertSame('hubspot-v4', data_get($config, 'target.id'));

        $this->assertSame(route('api.settings.hotglue.connect'), data_get($config, 'routes.connect'));
        $this->assertSame(route('api.settings.hotglue.disconnect'), data_get($config, 'routes.disconnect'));
    }
}
