<?php

namespace Tests\Unit\Domain\QuickStart\Tasks;

use Ds\Domain\QuickStart\Tasks\BrandingSetup;
use Tests\TestCase;

/** @group QuickStart */
class BrandingSetupTest extends TestCase
{
    /** @dataProvider isCompletedReturnsFalseWhenDefaultLogoDataProvider */
    public function testIsCompletedReturnsFalseWhenDefaultLogo(string $logo)
    {
        sys_set('default_logo', $logo);

        $this->assertFalse($this->app->make(BrandingSetup::class)->isCompleted());
    }

    public function isCompletedReturnsFalseWhenDefaultLogoDataProvider(): array
    {
        return [
            [''],
            ['https://givecloud.co/static/img/gc-logo.svg'],
            ['https://cdn.givecloud.co/static/etc/new-site-logo.svg'],
        ];
    }

    public function testIsCompletedReturnsTrueWhenLogo(): void
    {
        sys_set('default_logo', 'https://cdn.givecloud.co/some-customer-logo.jpg');

        $this->assertTrue($this->app->make(BrandingSetup::class)->isCompleted());
    }
}
