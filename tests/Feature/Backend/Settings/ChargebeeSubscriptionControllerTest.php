<?php

namespace Tests\Feature\Backend\Settings;

use ChargeBee\ChargeBee\Models\HostedPage as ChargeBeeHostedPage;
use Ds\Common\Chargebee\BillingPlansService;
use Ds\Common\Chargebee\Plans\ImpactPlan;
use Ds\Domain\MissionControl\MissionControlService;
use Exception;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** @group Chargebee */
class ChargebeeSubscriptionControllerTest extends TestCase
{
    use WithFaker;

    public function testUserCannotCheckoutIfNotPermission()
    {
        $this->actingAsUser()
            ->post(route('billing.chargebee.checkout'), ['plan_id' => 'some_plan_id'])
            ->assertNotFound();
    }

    public function testCallsUnderlyingServiceAndCatchesErrors()
    {
        $exception = 'Some Chargebee error occurred';

        $this->mock('chargebee')->shouldReceive('updateCustomer')->andThrow(new Exception($exception));

        $this->actingAsAdminUser()
            ->post(route('billing.chargebee.checkout'), ['plan_id' => 'some_plan_id'])
            ->assertJsonPath('error', $exception);
    }

    public function testCallsUnderlyingServiceAndReturnHostedPage()
    {
        $mock = $this->mock('chargebee');
        $mock->shouldReceive('updateCustomer')->andReturnTrue();
        $mock->shouldReceive('createCheckoutPageForPlan')->andReturn(new ChargeBeeHostedPage(['key' => 'value']));

        $this->actingAsAdminUser()
            ->post(route('billing.chargebee.checkout'), ['plan_id' => 'some_plan_id'])
            ->assertJsonPath('key', 'value');
    }

    /** @dataProvider erroneousParamsDataProvioder */
    public function testCallbackFailsWithoutRequestParams(array $params)
    {
        $this->actingAsAdminUser()
            ->get(route('billing.chargebee.callback'))
            ->assertRedirect(route('backend.settings.billing'))
            ->assertSessionHas('_flashMessages.error', 'An error occurred, please try again.');
    }

    public function erroneousParamsDataProvioder(): array
    {
        return [
            [['state' => 'succeeded']], // Missing all params (state & id)
            [['state' => 'succeeded']], // Missing ID
            [['id' => 'some_callback_id']], // Missing state
            [['state' => 'unsucceeded', 'id' => 'some_id']], // Invalid State.
        ];
    }

    public function testCallbackCatchesUnderlyingServiceError(): void
    {
        $this->mock('chargebee')->shouldReceive('hostedPage')->andThrow(new Exception('An error occurred'));

        $this->actingAsAdminUser()
            ->get(route('billing.chargebee.callback', [
                'state' => 'succeeded',
                'id' => 'invalid_id',
            ]))
            ->assertRedirect(route('backend.settings.billing'))
            ->assertSessionHas('_flashMessages.error', 'An error occurred');
    }

    public function testCallbackUpdatesCustomerAndSetsSubscriptionInMissionControl(): void
    {
        Http::fake();

        $site = $this->app->make(MissionControlService::class)->getSite();
        $site->client_id = 1;

        $pageId = $this->faker->uuid;

        $plan = new ImpactPlan;

        $page = new ChargeBeeHostedPage([
            'id' => $pageId,
            'content' => [
                'customer' => [
                    'billing_address' => [
                        'line1' => $this->faker->address,
                        'city' => $this->faker->city,
                        'zip' => $this->faker->postcode,
                        'state' => $this->faker->state,
                        'country' => $this->faker->country,
                    ],
                ],
                'subscription' => [
                    'id' => $this->faker->uuid,
                    'planId' => 'chargebee_plan_id',
                    'planAmount' => '349900',
                    'currencyCode' => 'USD',
                    'transactionFees' => 0.0125,
                    'billingPeriodUnit' => 'annually',
                ],
            ],
        ]);

        $this->mock('chargebee')->shouldReceive('hostedPage')->andReturn($page);

        $service = $this->mock(BillingPlansService::class);
        $service->shouldReceive('fromChargebeeId')->with('chargebee_plan_id')->andReturn($plan);
        $service->shouldReceive('currency')->andReturn('CAD');

        $this->actingAsAdminUser()
            ->get(route('billing.chargebee.callback', [
                'state' => 'succeeded',
                'id' => $pageId,
            ]))
            ->assertRedirect(route('backend.settings.billing'))
            ->assertSessionHas('_flashMessages.success', "🎉 &nbsp; Yay! You're all set!");
    }
}
