<?php

namespace Tests\Unit\Domain\Commerce\Gateways;

use Ds\Domain\Commerce\Enums\ContributionPaymentType;
use Ds\Domain\Commerce\SourceTokenUrlOptions;
use Tests\Concerns\InteractsWithStripe;
use Tests\StoryBuilder;
use Tests\TestCase;

class StripeGatewayTest extends TestCase
{
    use InteractsWithStripe;

    public function testGetSourceTokenUrlWithPaymentIntent(): void
    {
        $this->mockStripe('customers->retrieve')->andReturnStripe('customer');
        $this->mockStripe('paymentIntents->create')->andReturnStripe('payment-intent');

        $gateway = $this->getStripeGateway();
        $contribution = StoryBuilder::onetimeContribution()
            ->abandoningDonation()
            ->tokenizingPaymentMethod(['stripe_customer_id' => 'cus_M5TWaPJ8l2uJIC'])
            ->usingPaymentProvider($gateway->provider())
            ->create();

        $response = $gateway->getSourceTokenUrl($contribution->paymentMethod, null, null, new SourceTokenUrlOptions(['contribution' => $contribution]));

        $this->assertJsonable($response, [
            'id' => 'pi_3LN0R1EF6XkQa2YL17Apfx3a',
            'object' => 'payment_intent',
            'client_secret' => 'pi_3LN0R1EF6XkQa2YL17Apfx3a_secret_NNjyL280i2PbDyxlh4GsDz0Hm',
            'payment_method' => null,
        ]);
    }

    public function testGetSourceTokenUrlWithPaymentIntentAndWalletPay(): void
    {
        $this->mockStripe('paymentMethods->retrieve')->andReturnStripe('payment-method-with-card-no-customer');
        $this->mockStripe('customers->all')->andReturnStripe('customers');
        $this->mockStripe('paymentIntents->create')->andReturnStripe('payment-intent');

        $gateway = $this->getStripeGateway();
        $contribution = StoryBuilder::onetimeContribution()
            ->abandoningDonation()
            ->tokenizingPaymentMethod(['billing_email' => 'bob@example.com'])
            ->usingPaymentProvider($gateway->provider())
            ->create();

        $gateway->request()->merge([
            'payment_type' => ContributionPaymentType::WALLET_PAY,
            'payment_method' => 'pm_1LN0QzEF6XkQa2YLvajttYm5',
        ]);

        $response = $gateway->getSourceTokenUrl($contribution->paymentMethod, null, null, new SourceTokenUrlOptions(['contribution' => $contribution]));

        $this->assertJsonable($response, [
            'id' => 'pi_3LN0R1EF6XkQa2YL17Apfx3a',
            'object' => 'payment_intent',
            'client_secret' => 'pi_3LN0R1EF6XkQa2YL17Apfx3a_secret_NNjyL280i2PbDyxlh4GsDz0Hm',
            'payment_method' => 'pm_1LN0QzEF6XkQa2YLvajttYm5',
        ]);
    }

    public function testGetSourceTokenUrlWithSetupIntent(): void
    {
        $this->mockStripe('customers->all')->andReturnStripe('customers-empty');
        $this->mockStripe('customers->create')->andReturnStripe('customer');
        $this->mockStripe('setupIntents->create')->andReturnStripe('setup-intent');

        $gateway = $this->getStripeGateway();
        $supporter = StoryBuilder::supporter()
            ->tokenizingPaymentMethod(['billing_email' => 'never.gonna.be.another.email.like.this@example.com'])
            ->usingPaymentProvider($gateway->provider())
            ->create();

        $response = $gateway->getSourceTokenUrl($supporter->paymentMethods[0]);

        $this->assertJsonable($response, [
            'id' => 'seti_1LNJR7EF6XkQa2YLD0MnEikv',
            'object' => 'setup_intent',
            'client_secret' => 'seti_1LNJR7EF6XkQa2YLD0MnEikv_secret_M5UP2LhZNKsBXutnmt1YM46ikPtC0Kb',
        ]);
    }
}
