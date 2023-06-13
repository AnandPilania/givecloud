<?php

namespace Tests\Unit\Domain\Settings\Integrations\Config;

use Ds\Domain\Settings\Integrations\Config\DoubleTheDonationIntegrationSettingsConfig;
use Tests\TestCase;

class DoubleTheDonationSettingsConfigTest extends TestCase
{
    /** @dataProvider booleanDataProvider */
    public function testFeatureIsAvailableReturnsConfigState(bool $configState): void
    {
        sys_set('feature_double_the_donation', $configState);

        $response = $this->app->make(DoubleTheDonationIntegrationSettingsConfig::class)->isAvailable();

        $this->assertSame($response, $configState);
    }

    /** @dataProvider booleanDataProvider */
    public function testIsInstalledReturnsConfigState(bool $configState): void
    {
        sys_set('double_the_donation_enabled', $configState);

        $response = $this->app->make(DoubleTheDonationIntegrationSettingsConfig::class)->isInstalled();

        $this->assertSame($response, $configState);
    }

    public function booleanDataProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }
}
