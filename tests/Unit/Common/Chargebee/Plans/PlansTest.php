<?php

namespace Tests\Unit\Common\Chargebee\Plans;

use ChargeBee\ChargeBee\Models\Plan as ChargeBeePlan;
use Ds\Common\Chargebee\Plans\ImpactPlan;
use Ds\Common\Chargebee\Plans\ImpactPlusPlan;
use Ds\Common\Chargebee\Plans\LitePlan;
use Ds\Repositories\ChargebeeRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

/** @group Chargebee */
class PlansTest extends TestCase
{
    public function testPlansHasDescription(): void
    {
        $this->assertSame('Self-serve fundraising with full access to how to articles and videos', (new LitePlan)->description());
        $this->assertSame('Expert service and proactive TRUSTRAISING optimization ', (new ImpactPlan)->description());
        $this->assertSame('For our enterprise level subscribers who may need a little more', (new ImpactPlusPlan)->description());
    }

    public function testPlansHasFeatures(): void
    {
        $this->assertContains('2.50% Platform Fee', (new LitePlan)->features());
        $this->assertContains('2% Platform Fee', (new ImpactPlan)->features());
        $this->assertContains('< 1.25% Platform Fee', (new ImpactPlusPlan)->features());
    }

    public function testPlansHasLink(): void
    {
        $this->planMocks();

        $this->assertStringContainsString("javascript:j.createChargeBeeCheckout('lite_monthly_id')", (new LitePlan)->checkoutLink());
        $this->assertStringContainsString("javascript:j.createChargeBeeCheckout('impact_monthly_id')", (new ImpactPlan)->checkoutLink());
    }

    public function testImpactPlusHasNoLink(): void
    {
        $this->assertEmpty((new ImpactPlusPlan)->checkoutLink());
    }

    public function testHasPrice(): void
    {
        $this->planMocks();

        $this->assertTrue((new LitePlan)->hasPrice());
        $this->assertTrue((new ImpactPlan)->hasPrice());
        $this->assertFalse((new ImpactPlusPlan)->hasPrice());
    }

    private function planMocks(): Collection
    {
        Config::set('services.chargebee.plans.*.monthly.lite', 'lite_monthly_id');
        Config::set('services.chargebee.plans.*.monthly.impact', 'impact_monthly_id');

        $plans = collect([
            new ChargeBeePlan(['id' => 'lite_monthly_id']),
            new ChargeBeePlan(['id' => 'impact_monthly_id']),
        ]);

        $this->mock(ChargebeeRepository::class)->shouldReceive('getPlans')->andReturn($plans);

        return $plans;
    }
}
