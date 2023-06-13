<?php

namespace Tests\Unit\Domain\Commerce\Importers;

use Carbon\Carbon;
use Ds\Domain\Commerce\GatewayFactory;
use Ds\Domain\Commerce\Gateways\StripeGateway;
use Ds\Domain\Commerce\Importers\StripeImporter;
use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Models\Member as Account;
use Ds\Models\Payment;
use Ds\Models\Product;
use Ds\Models\Variant;
use Illuminate\Support\Facades\File;
use Stripe\Charge as StripeCharge;
use Stripe\Customer as StripeCustomer;
use Stripe\Subscription;
use Tests\TestCase;

class StripeImporterTest extends TestCase
{
    public function testImportingCustomerWithEmailBelongingToAnAccount(): void
    {
        $account = Account::factory()->create();
        $product = Product::factory()->create();
        $variant = Variant::factory()->create();
        $product->variants()->save($variant);

        $charge = $this->getStripeChargeWithoutAnInvoice();
        $customer = $this->getStripeCustomerWithACard(['email' => $account->email]);
        $payment = $this->importPaymentFromGateway($variant, $charge, $customer);

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertSame($account->getKey(), $payment->account->getKey());
    }

    public function testImportingCustomerWithEmailNotBelongingToAnAccount(): void
    {
        $product = Product::factory()->create();
        $variant = Variant::factory()->create();
        $product->variants()->save($variant);

        $charge = $this->getStripeChargeWithoutAnInvoice();
        $customer = $this->getStripeCustomerWithACard();
        $payment = $this->importPaymentFromGateway($variant, $charge, $customer);

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertSame($customer->email, $payment->account->email);
    }

    public function testImportingCustomerWithInvoiceAndEmailBelongingToAnAccount(): void
    {
        $product = Product::factory()->create();
        $variant = Variant::factory()->create();
        $product->variants()->save($variant);

        $charge = $this->getStripeChargeWithAnInvoice();
        $customer = $this->getStripeCustomerWithACard();
        $this->mockGatewayFactory($charge);
        $payment = $this->importPaymentFromGateway($variant, $charge, $customer);

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertSame($customer->email, $payment->account->email);
    }

    private function importPaymentFromGateway(Variant $variant, StripeCharge $charge, ?StripeCustomer $customer = null): ?Payment
    {
        $importer = app()->makeWith(StripeImporter::class, [
            'provider' => PaymentProvider::factory()->stripe()->create(),
            'onetimeVariantId' => $variant->getKey(),
        ]);

        return $importer->importPaymentFromGateway([$charge, $customer]);
    }

    private function getFakeStripeSubscription(int $amount = 2500): Subscription
    {
        $subscription = new Subscription('sub_1234');
        $subscription->created = Carbon::now();
        $subscription->status = 'active';
        $subscription->ended_at = Carbon::now();
        $subscription->current_period_end = Carbon::now()->addYear();
        $subscription->plan = (object) [
            'name' => 'a monthly plan',
            'amount' => $amount,
            'interval' => 'month',
            'currency' => 'usd',
        ];

        return $subscription;
    }

    private function getStripeChargeWithAnInvoice(): StripeCharge
    {
        return $this->getStripeCharge('charge-with-invoice');
    }

    private function getStripeChargeWithoutAnInvoice(): StripeCharge
    {
        return $this->getStripeCharge('charge-without-invoice');
    }

    private function getStripeCharge(string $stripeFixtureJsonFile): StripeCharge
    {
        return StripeCharge::constructFrom(
            json_decode(File::get(base_path("tests/fixtures/stripe/$stripeFixtureJsonFile.json")), true)
        );
    }

    private function getStripeCustomerWithACard(array $attributes = []): StripeCustomer
    {
        return StripeCustomer::constructFrom(array_merge(
            json_decode(File::get(base_path('tests/fixtures/stripe/customer-with-card.json')), true),
            $attributes
        ));
    }

    private function mockGatewayFactory($charge): void
    {
        $stripeGatewayMock = $this->createPartialMock(StripeGateway::class, ['getInvoice', 'getSubscription']);
        $stripeGatewayMock
            ->expects($this->once())
            ->method('getInvoice')
            ->willReturn($charge->invoice);
        $stripeGatewayMock
            ->expects($this->once())
            ->method('getSubscription')
            ->willReturn($this->getFakeStripeSubscription());

        $gatewayFactoryMock = $this->createPartialMock(GatewayFactory::class, ['make']);
        $gatewayFactoryMock
            ->expects($this->once())
            ->method('make')
            ->willReturn($stripeGatewayMock);

        $this->app->instance(GatewayFactory::class, $gatewayFactoryMock);
    }
}
