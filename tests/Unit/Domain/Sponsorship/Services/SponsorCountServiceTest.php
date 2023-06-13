<?php

namespace Tests\Unit\Domain\Sponsorship\Services;

use Ds\Domain\Sponsorship\Models\Sponsor;
use Ds\Domain\Sponsorship\Models\Sponsorship;
use Ds\Domain\Sponsorship\Services\SponsorCountService;
use Tests\TestCase;

/**
 * @group sponsorship
 */
class SponsorCountServiceTest extends TestCase
{
    public function testWithActiveAndEndedSponsors(): void
    {
        $sponsorship = Sponsorship::factory()
            ->has(Sponsor::factory()->count(3))
            ->has(Sponsor::factory()->ended()->count(8))
            ->create();

        $this->app->make(SponsorCountService::class)->update($sponsorship);

        $this->assertSame(3, $sponsorship->sponsor_count);
    }
}
