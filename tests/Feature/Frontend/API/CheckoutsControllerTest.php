<?php

namespace Tests\Feature\Frontend\API;

use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Models\Order;
use Ds\Models\PaymentMethod;
use Tests\TestCase;

class CheckoutsControllerTest extends TestCase
{
    public function testUsingPaymentMethodWhenOverridingPaymentProvidersIsAllowed(): void
    {
        $paymentMethod = PaymentMethod::factory()->create();

        $cart = Order::factory()->create();
        $cart->populateMember($paymentMethod->member);

        sys_set(['allow_overriding_payment_providers' => true]);

        $this->postJson(route('api.checkouts.capture_token', $cart->client_uuid), [
            'provider' => 'paymentmethod',
            'payment_type' => 'payment_method',
            'payment_method' => $paymentMethod->id,
        ]);

        $cart->refresh();

        $this->assertSame($paymentMethod->id, $cart->payment_method_id);
        $this->assertSame($paymentMethod->paymentProvider->provider, $cart->paymentProvider->provider);
    }

    public function testOverridingPaymentProvidersWhileCapturingToken(): void
    {
        $cart = Order::factory()->create();

        $paymentProvider = PaymentProvider::factory()->create();
        $nmiPaymentProvider = PaymentProvider::factory()->nmi()->create();

        sys_set([
            'credit_card_provider' => $nmiPaymentProvider->provider,
            'allow_overriding_payment_providers' => true,
        ]);

        $this->postJson(route('api.checkouts.capture_token', $cart->client_uuid), [
            'payment_type' => 'credit_card',
            'provider' => $paymentProvider->provider,
        ]);

        $cart->refresh();

        $this->assertSame($paymentProvider->provider, $cart->paymentProvider->provider);
    }
}
