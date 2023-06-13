<?php

namespace Tests\Unit\Listeners\Order;

use Ds\Events\OrderWasRefunded;
use Ds\Listeners\Order\UpdateFundraisingAggregates;
use Tests\StoryBuilder;
use Tests\TestCase;

class UpdateFundraisingAggregatesTest extends TestCase
{
    public function testShouldQueueWhenAttachedToFundraisingPage(): void
    {
        $contribution = StoryBuilder::onetimeContribution()
            ->fromFundraisingPage()
            ->create();

        $shouldQueue = app(UpdateFundraisingAggregates::class)->shouldQueue(
            new OrderWasRefunded($contribution)
        );

        $this->assertTrue($shouldQueue);
    }

    public function testShouldNotQueueWhenNotAttachedToFundraisingPage(): void
    {
        $contribution = StoryBuilder::onetimeContribution()->create();

        $shouldQueue = app(UpdateFundraisingAggregates::class)->shouldQueue(
            new OrderWasRefunded($contribution)
        );

        $this->assertFalse($shouldQueue);
    }

    public function testFundraisingPageAggregatesAreUpdated(): void
    {
        $contribution = StoryBuilder::onetimeContribution()
            ->fromFundraisingPage()
            ->create();

        $contribution->refund();

        $this->assertSame(0.0, $contribution->items[0]->fundraisingPage->amount_raised);
    }
}
