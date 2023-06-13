<?php

namespace Tests\Unit\Common\Chargebee;

use ChargeBee\ChargeBee\Models\Plan as ChargeBeePlan;
use Ds\Common\Chargebee\BillingPlansService;
use Ds\Common\Chargebee\Plans\LitePlan;
use Ds\Repositories\ChargebeeRepository;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

/** @group Chargebee */
class BillingPlansServiceTest extends TestCase
{
    public function testAllReturnsAllPlans(): void
    {
        $plans = $this->app->make(BillingPlansService::class)->all();

        $this->assertCount(3, $plans);
    }

    public function testChargebeeIdsReturnsAllConfiguredIdsForCurrency(): void
    {
        sys_set('dpo_currency', 'CAD');

        Config::set('services.chargebee.plans.cad.annually.lite', 'lite_cad_annual_plan');
        Config::set('services.chargebee.plans.*.annually.lite', 'lite_usd_annual_plan');

        $ids = $this->app->make(BillingPlansService::class)->chargebeeIds();

        $this->assertContains('lite_cad_annual_plan', $ids);
        $this->assertNotContains('lite_usd_annual_plan', $ids);
    }

    public function testFromChargebeeIdReturnsNullIfNoMatch(): void
    {
        $plans = collect([
            new ChargeBeePlan(['id' => 'unmatched_some_id']),
        ]);

        $this->partialMock(ChargebeeRepository::class)->shouldReceive('getPlans')->andReturn($plans);

        $this->assertNull($this->app->make(BillingPlansService::class)->fromChargebeeId('some_id'));
    }

    public function testFromChargebeeIdReturnsPlans(): void
    {
        $chargebeePlanId = 'lite_annual_plan';

        $plans = collect([
            new ChargeBeePlan(['id' => $chargebeePlanId]),
        ]);

        $this->partialMock(ChargebeeRepository::class)->shouldReceive('getPlans')->andReturn($plans);

        Config::set('services.chargebee.plans.*.annually.lite', 'lite_annual_plan');

        $plan = $this->app->make(BillingPlansService::class)->fromChargebeeId('lite_annual_plan');

        $this->assertInstanceOf(LitePlan::class, $plan);
    }

    /** @dataProvider currencyDataProvider */
    public function testCurrencyReturnsScopedCurrency(string $currency, string $expected): void
    {
        sys_set('dpo_currency', $currency);

        $this->assertSame($expected, $this->app->make(BillingPlansService::class)->currency());
    }

    public function currencyDataProvider(): array
    {
        return [
            ['CAD', 'CAD'],
            ['USD', 'USD'],
            ['EUR', 'USD'],
            ['AUD', 'USD'],
            ['CNY', 'USD'],
            ['EUR', 'USD'],
            ['MXN', 'USD'],
            ['CHF', 'USD'],
        ];
    }
}
