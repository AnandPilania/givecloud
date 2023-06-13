<?php

namespace Tests\Unit\Domain\QuickStart\Tasks;

use Ds\Domain\QuickStart\Tasks\CustomizeDonorPortal;
use Tests\TestCase;

/** @group QuickStart */
class CustomizeDonorPortalTest extends TestCase
{
    public function testIsCompletedReturnsFalseWhenNoSettingChanged(): void
    {
        $this->assertFalse($this->app->make(CustomizeDonorPortal::class)->isCompleted());
    }

    /** @dataProvider isCompletedReturnsTrueWhenAnySettingIsChangedDataProvider */
    public function testIsCompletedReturnsTrueWhenAnySettingIsChanged(string $setting): void
    {
        sys_set($setting, 'any_other_value');

        $this->assertTrue($this->app->make(CustomizeDonorPortal::class)->isCompleted());
    }

    public function isCompletedReturnsTrueWhenAnySettingIsChangedDataProvider(): array
    {
        return [
            ['referral_sources_isactive'],
            ['referral_sources_other'],
            ['referral_sources_options'],
            ['donor_title'],
            ['force_country'],
            ['allow_account_types_on_web'],
            ['nps_enabled'],
            ['marketing_optout_reason_required'],
        ];
    }
}
