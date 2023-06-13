<?php

namespace Tests\Feature\Backend\Api;

use Tests\TestCase;

class FundraiseEarlyAccessControllerTest extends TestCase
{
    public function earlyAccessStateDataProvider(): array
    {
        return [
            ['0'],
            ['1'],
        ];
    }

    public function testStoreCanStoreEarlyAccessState(): void
    {
        $this->assertSame('0', sys_get('fundraise_early_access_requested'));

        $this
            ->actingAsSuperUser()
            ->postJson(route('fundraise.early-access.store'))
            ->assertJsonPath('requested', '1');

        $this->assertSame('1', sys_get('fundraise_early_access_requested'));
    }
}
