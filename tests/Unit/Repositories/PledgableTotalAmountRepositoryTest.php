<?php

namespace Tests\Unit\Repositories;

use Carbon\Carbon;
use Ds\Models\Member as Supporter;
use Ds\Models\Order;
use Ds\Models\Pledge;
use Ds\Models\PledgeCampaign;
use Ds\Models\Product;
use Ds\Models\Variant;
use Ds\Repositories\PledgableTotalAmountRepository;
use Tests\TestCase;

class PledgableTotalAmountRepositoryTest extends TestCase
{
    /**
     * @dataProvider amountIncludesPledgesAndContributionsWithinCampaignBoundariesProvider
     */
    public function testAmountIncludesPledgesAndContributionsWithinCampaignBoundaries(float $expectedAmount, ?string $startDate, ?string $endDate): void
    {
        Carbon::setTestNow('2021-10-13 12:00:00');

        $amount = $this->app->make(PledgableTotalAmountRepository::class)->get(
            $this->generatePledgeCampaignIncludingPledgesAndContributions($startDate, $endDate)
        );

        $this->assertSame($expectedAmount, $amount);
    }

    public function amountIncludesPledgesAndContributionsWithinCampaignBoundariesProvider(): array
    {
        return [
            [320, '2021-10-11', '2021-10-15'],
            [370, '2021-10-06', '2021-10-15'],
            [370, '2021-10-11', '2021-10-20'],
            [420, '2021-10-06', '2021-10-20'],
        ];
    }

    public function testContributionAmountsRelatedToAPledgeAreNotIncludedInAmountTwice(): void
    {
        $this->expectNotToPerformAssertions();
    }

    private function generatePledgeCampaignIncludingPledgesAndContributions(?string $startDate, ?string $endDate): PledgeCampaign
    {
        $campaign = PledgeCampaign::factory()
            ->hasAttached(
                Product::factory()
                    ->donation()
                    ->allowOutOfStock()
                    ->has(Variant::factory()->donation(), 'variants'),
            )->create([
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

        $this->travel(-7)->days();

        foreach (range(1, 14) as $index) {
            $this->generatePaidOrder($campaign);
            $this->travel(1)->days();
        }

        $this->travel(-7)->days();

        $this->generateOrder($campaign);
        $this->generateOrder($campaign);

        $this->generatePledge($campaign);
        $this->generatePledge($campaign);
        $this->generatePledge($campaign);

        // $100 pledge amount + $0 of the contribution amounts
        tap(Supporter::factory()->create(), function (Supporter $supporter) use ($campaign) {
            $this->generatePledge($campaign, $supporter, 100);
            $this->generatePaidOrder($campaign, $supporter);
            $this->generatePaidOrder($campaign, $supporter, true);
            $this->generatePaidOrder($campaign, $supporter);
        });

        // $20 pledge amount + $10 of the contribution amounts
        tap(Supporter::factory()->create(), function (Supporter $supporter) use ($campaign) {
            $this->generatePledge($campaign, $supporter, 20);
            $this->generatePaidOrder($campaign, $supporter);
            $this->generatePaidOrder($campaign, $supporter, true);
            $this->generatePaidOrder($campaign, $supporter);
        });

        return $campaign;
    }

    private function generateOrder(PledgeCampaign $campaign, ?Supporter $supporter = null, bool $recurring = false, float $amount = 10): Order
    {
        $order = Order::factory()
            ->for($supporter ?? Supporter::factory(), 'member')
            ->create();

        $order->addItem([
            'variant_id' => $campaign->products[0]->variants[0]->getKey(),
            'amt' => $amount,
            'recurring_frequency' => $recurring ? 'monthly' : null,
            'recurring_day' => 1,
            'recurring_with_initial_charge' => false,
        ]);

        return $order;
    }

    private function generatePaidOrder(PledgeCampaign $campaign, ?Supporter $supporter = null, bool $recurring = false, float $amount = 10): Order
    {
        $order = $this->generateOrder($campaign, $supporter, $recurring, $amount);
        $order->confirmationdatetime = now();
        $order->save();

        return $order;
    }

    private function generatePledge(PledgeCampaign $campaign, ?Supporter $supporter = null, float $amount = 50): Pledge
    {
        return Pledge::factory()
            ->for($campaign, 'campaign')
            ->for($supporter ?? Supporter::factory(), 'account')
            ->create([
                'total_amount' => $amount,
                'currency_code' => (string) currency(),
                'start_date' => $campaign->start_date,
                'end_date' => $campaign->end_date,
            ]);
    }
}
