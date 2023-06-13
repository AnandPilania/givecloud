<?php

namespace Tests\Unit\Domain\Sponsorship\Listeners;

use Ds\Domain\Sponsorship\Events\SponsorWasEnded;
use Ds\Domain\Sponsorship\Events\SponsorWasStarted;
use Ds\Domain\Sponsorship\Listeners\NotifySponsorshipStart;
use Ds\Domain\Sponsorship\Listeners\UpdateSponsorCount;
use Ds\Domain\Sponsorship\Models\Sponsor;
use Ds\Domain\Sponsorship\Models\Sponsorship;
use Tests\TestCase;

/**
 * @group sponsorship
 */
class UpdateSponsorCountTest extends TestCase
{
    /**
     * @dataProvider sponsorCountWasUpdatedProvider
     */
    public function testSponsorCountWasUpdated(string $eventClassName): void
    {
        $sponsorship = Sponsorship::factory()->create();
        $sponsorship->sponsors()->save(
            $sponsor = Sponsor::factory()->make()
        );

        $listener = $this->app->make(UpdateSponsorCount::class);
        $listener->handle(new $eventClassName($sponsor));

        $this->assertSame(1, $sponsorship->refresh()->sponsor_count);
    }

    public function sponsorCountWasUpdatedProvider(): array
    {
        return [
            [SponsorWasStarted::class],
            [SponsorWasEnded::class],
        ];
    }

    public function testSponsorIsNotNotifiedWhenOptionIsPassed(): void
    {
        $listener = $this->app->make(NotifySponsorshipStart::class);
        $this->assertFalse($listener->handle(new SponsorWasStarted(Sponsor::factory()->make(), ['do_not_send_email' => true])));
    }
}
