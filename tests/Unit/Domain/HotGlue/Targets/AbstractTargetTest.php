<?php

namespace Tests\Unit\Domain\HotGlue\Targets;

use Ds\Domain\Shared\Exceptions\MessageException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\Fakes\HotGlue\ImplementedTarget;
use Tests\Fakes\HotGlue\NotImplementedTarget;
use Tests\TestCase;

/**
 * @group hotglue
 */
class AbstractTargetTest extends TestCase
{
    public function testIsEnabledReturnsFeatureIsEnabled(): void
    {
        $instance = $this->app->make(ImplementedTarget::class);

        $this->assertFalse($instance->isEnabled());

        sys_set('feature_hotglue_implemented', true);

        $this->assertTrue($instance->isEnabled());
    }

    public function testIsLinkedReturnsLinkConfigStatus(): void
    {
        $instance = $this->app->make(ImplementedTarget::class);

        $this->assertFalse($instance->isLinked());

        sys_set('hotglue_implemented_linked', true);

        $this->assertTrue($instance->isLinked());
    }

    public function testIsConnectedReturnsTrueWhenTargetIsInResponse(): void
    {
        Config::set('services.hotglue.implemented.target_id', 'my_target_id');

        Http::fake(function () {
            return Http::response([
                [
                    'target' => 'my_target_id',
                    'domain' => 'salesforce.com',
                    'label' => 'Salesforce',
                    'version' => 'v2',
                ],
            ]);
        });

        $instance = $this->app->make(ImplementedTarget::class);

        $this->assertTrue($instance->isConnected());
    }

    public function testIsConnectedReturnsFalseWhenTargetNotFoundInResponse(): void
    {
        Cache::flush();

        Config::set('services.hotglue.implemented.target_id', 'my_target_id');

        Http::fake(function () {
            return Http::response([
                [
                    'target' => 'unmatched_target_id',
                    'domain' => 'salesforce.com',
                    'label' => 'Salesforce',
                    'version' => 'v2',
                ],
                [
                    'target' => 'other_unmatched_target_id',
                    'domain' => 'mailchimp.com',
                    'label' => 'Mailchimp',
                    'version' => 'v2',
                ],
            ]);
        });

        $instance = $this->app->make(ImplementedTarget::class);

        $this->assertFalse($instance->isConnected());
    }

    public function testTestThrowsExceptionWhenConfigNotImplemented(): void
    {
        $this->expectException(MessageException::class);

        $instance = $this->app->make(NotImplementedTarget::class);

        $instance->config();
    }

    public function testConfigReturnsConfig(): void
    {
        Config::set('services.hotglue.implemented', [
            'foo' => 'bar',
        ]);

        $instance = $this->app->make(ImplementedTarget::class);

        $this->assertArrayHasKey('foo', $instance->config());
    }
}
