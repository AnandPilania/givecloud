<?php

namespace Tests\Feature\Backend\Api\Dashboard;

use Carbon\Carbon;
use Ds\Models\Member;
use Ds\Models\Order;
use Ds\Models\OrderItem;
use Ds\Models\Product;
use Ds\Models\Transaction;
use Ds\Models\Variant;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/** @group dashboard */
class ChartsControllerTest extends TestCase
{
    use WithFaker;

    public function testUnauthorizedReturnsJsonError(): void
    {
        $this->actingAsUser()
            ->get(route('dashboard.charts'))
            ->assertJsonPath('error', 'You are not authorized to perform this action.');
    }

    public function testInvokeReturnsChartsData(): void
    {
        // Member Growth initial.
        Member::factory()->create(['created_at' => Carbon::now()->subYear()]);

        // Completed contributions. (2)
        $order = Order::factory()->paid()->completed()->create();

        $product = Product::factory()->create();

        // Completed contribution with products for bestseller
        $anotherOrder = Order::factory()->paid()
            ->has(OrderItem::factory()->for(
                Variant::factory()->for($product)
            ), 'items')
            ->completed()
            ->create(['ordered_at' => toUtc('today')]);

        // Empty Cart
        Order::factory()->create();

        // Carts w/ Items
        Order::factory(2)->hasItems()->create();

        // Carts in Checkout
        Order::factory(3)->hasItems()->create(['billingemail' => $this->faker->email]);

        $transactionRevenues = Transaction::factory(2)->paid()->create(['order_time' => toUtc('now')])
            ->sum('functional_total');

        $revenues = collect([$order, $anotherOrder])->sum('functional_total');

        $this->actingAsAdminUser()
            ->get(route('dashboard.charts'))
            ->assertJsonStructure([
                'revenue_chart',
                'best_seller_chart_data',
                'engagement_chart_data',
            ])->assertJsonPath('revenue_chart.60.order_date', fromLocal('now')->toDateString())
            ->assertJsonPathEquals('revenue_chart.60.one_time', round($revenues, 2))
            ->assertJsonPathEquals('revenue_chart.60.recurring', round($transactionRevenues, 2))
            ->assertJsonPath('engagement_chart_data.0.label', 'Empty Carts')
            ->assertJsonPath('engagement_chart_data.0.value', 1)
            ->assertJsonPath('engagement_chart_data.1.label', 'Carts w/ Items')
            ->assertJsonPath('engagement_chart_data.1.value', 2)
            ->assertJsonPath('engagement_chart_data.2.label', 'In Checkout')
            ->assertJsonPath('engagement_chart_data.2.value', 3)
            ->assertJsonPath('engagement_chart_data.3.label', 'Completed Contributions')
            ->assertJsonPath('engagement_chart_data.3.value', 2)
            ->assertJsonPath('best_seller_chart_data.0.name', $product->name)
            ->assertJsonPath('best_seller_chart_data.0.sales_count', 1)
            ->assertJsonPath('account_growth_chart_data_30day.0.created_at', fromLocal('now')->subDays(30)->toDateString())
            ->assertJsonPath('account_growth_chart_data_30day.1.growth', 1)
            ->assertJsonPath('account_growth_chart_data_30day.30.created_at', fromLocal('now')->toDateString())
            ->assertJsonPath('account_growth_chart_data_30day.30.growth', 9);
    }
}
