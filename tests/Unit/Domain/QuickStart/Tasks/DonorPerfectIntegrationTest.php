<?php

namespace Tests\Unit\Domain\QuickStart\Tasks;

use Ds\Domain\MissionControl\MissionControlService;
use Ds\Domain\QuickStart\Tasks\DonorPerfectIntegration;
use Ds\Domain\Settings\Integrations\Config\DonorPerfectIntegrationSettingsConfig;
use Tests\TestCase;

/** @group QuickStart */
class DonorPerfectIntegrationTest extends TestCase
{
    public function testIntegrationIsActiveIfPartnerIsDP(): void
    {
        $site = $this->app->make(MissionControlService::class)->getSite();
        $site->partner->identifier = 'dp';

        $this->mock(MissionControlService::class)->shouldReceive('getSite')->andReturn($site);

        $this->assertSame('dp', $site->partner->identifier);
        $this->assertTrue($this->app->make(DonorPerfectIntegration::class)->isActive());
    }

    public function testIntegrationIsNotActiveIfPartnerIsNotDP(): void
    {
        $site = $this->app->make(MissionControlService::class)->getSite();

        $this->assertNotSame('dp', $site->partner->identifier);

        $this->assertFalse($this->app->make(DonorPerfectIntegration::class)->isActive());
    }

    public function testIsCompletedReturnsFalseWhenDPIsNotConnected(): void
    {
        $this->assertFalse($this->app->make(DonorPerfectIntegration::class)->isCompleted());
    }

    public function testIsCompletedReturnsTrueWhenDPIsConnected(): void
    {
        $this->mock(DonorPerfectIntegrationSettingsConfig::class)->shouldReceive('isInstalled')->andReturnTrue();

        $this->assertTrue($this->app->make(DonorPerfectIntegration::class)->isCompleted());
    }
}
