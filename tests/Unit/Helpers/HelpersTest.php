<?php

namespace Tests\Unit\Helpers;

use Tests\TestCase;

class HelpersTest extends TestCase
{
    /**
     * @dataProvider flagDataProvider
     */
    public function testFlag(string $countryCode, string $expected): void
    {
        $this->assertSame($expected, flag($countryCode));
    }

    public function flagDataProvider(): array
    {
        return [
            ['ca', $this->flagUrl('canada')],
            ['us', $this->flagUrl('united-states')],
            ['hk', $this->flagUrl('default')],
        ];
    }

    private function flagUrl(string $flagFileName): string
    {
        return "https://cdn.givecloud.co/static/flag/$flagFileName.png";
    }
}
