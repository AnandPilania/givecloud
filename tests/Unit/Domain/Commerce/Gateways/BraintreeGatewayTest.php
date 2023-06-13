<?php

namespace Tests\Unit\Domain\Commerce\Gateways;

use Braintree\CustomerGateway;
use Braintree\Gateway;
use Braintree\PaymentMethodGateway;
use Braintree\TransactionGateway;
use Ds\Domain\Commerce\Exceptions\GatewayException;
use Ds\Domain\Commerce\Exceptions\PaymentException;
use Ds\Domain\Commerce\Exceptions\RefundException;
use Ds\Domain\Commerce\Gateways\BraintreeGateway;
use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Domain\Commerce\Money;
use Ds\Domain\Commerce\Responses\ErrorResponse;
use Ds\Domain\Commerce\SourceTokenChargeOptions;
use Ds\Models\Order;
use Ds\Models\PaymentMethod;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class BraintreeGatewayTest extends TestCase
{
    use WithFaker;

    public function testReturnsName(): void
    {
        $this->assertSame('braintree', $this->getGateway()->name());
    }

    public function testReturnsDisplayName(): void
    {
        $this->assertSame('Braintree', $this->getGateway()->getDisplayName());
    }

    public function testGatewayInstanceIsSingleton(): void
    {
        $gateway = $this->getGateway();
        $this->assertSame($gateway->gateway(), $gateway->gateway());
    }

    /**
     * @dataProvider merchantIdConfigsDataProvider
     */
    public function testCanReturnMerchantIdForCurrency(array $config, string $expected, ?string $currency = null): void
    {
        $gateway = $this->getGateway();
        $gateway->provider()->setAttribute('config', [
            'merchant_account_id' => $config,
        ]);

        $this->assertSame($expected, $gateway->getMerchantAccountIdForCurrency($currency));
    }

    public function merchantIdConfigsDataProvider(): array
    {
        return [
            [['no_currency'], 'no_currency'],
            [['CAD' => 'cad_merchant'], 'cad_merchant'],
            [['CAD' => 'cad_merchant', 'USD' => 'usd_merchant'], 'usd_merchant'],
            [['CAD' => 'cad_merchant', 'USD' => 'usd_merchant'], 'usd_merchant', 'USD'],
            [['CAD' => 'cad_merchant', 'USD' => 'usd_merchant'], 'cad_merchant', 'CAD'],
        ];
    }

    public function testGetCaptureTokenUrlReturnsError(): void
    {
        $this->assertInstanceOf(ErrorResponse::class, $this->getGatewayForTransaction()->getCaptureTokenUrl(Order::factory()->create()));
    }

    public function testChargeCaptureTokenWithoutTokenThrowsException(): void
    {
        $this->expectException(GatewayException::class);

        $gateway = $this->getGatewayForTransaction();
        $gateway->request()->offsetUnset('token');

        $gateway->chargeCaptureToken(Order::factory()->create());
    }

    public function testChargeCaptureTokenWithoutNonceThrowsException(): void
    {
        $this->expectException(GatewayException::class);

        $gateway = $this->getGatewayForTransaction();
        $gateway->request()->merge(['token' => []]);

        $gateway->chargeCaptureToken(Order::factory()->create());
    }

    public function testCreateSourceTokenWithoutTokenThrowsException(): void
    {
        $this->expectException(GatewayException::class);

        $gateway = $this->getGatewayForTransaction();
        $gateway->request()->offsetUnset('token');

        $gateway->createSourceToken(PaymentMethod::factory()->create());
    }

    public function testCreateSourceTokenWithoutNonceThrowsException(): void
    {
        $this->expectException(GatewayException::class);

        $gateway = $this->getGatewayForTransaction();
        $gateway->request()->merge(['token' => []]);

        $gateway->chargeCaptureToken(Order::factory()->create());
    }

    public function testCanChargeCaptureToken(): void
    {
        $order = Order::factory()->create();
        $response = $this->getGatewayForTransaction()->chargeCaptureToken($order);

        $this->assertTrue($response->isCompleted());
        $this->assertSame('1', $response->getResponse());
    }

    public function testRaisesErrorIfChargeCaptureTokenFails(): void
    {
        $this->expectException(PaymentException::class);

        $response = $this->getGatewayForTransaction('transaction', false)
            ->chargeCaptureToken(Order::factory()->create());

        $this->assertFalse($response->isCompleted());
    }

    public function testCanRefundTransaction(): void
    {
        $gateway = $this->getGatewayForTransaction('refund');
        $response = $gateway->refundCharge('some_transaction_id');

        $this->assertTrue($response->isCompleted());
        $this->assertSame('succeeded', $response->getResponse());
    }

    public function testRefundTransactionThrowsExceptionOnError(): void
    {
        $this->expectException(RefundException::class);

        $gateway = $this->getGatewayForTransaction('refund', false);
        $response = $gateway->refundCharge('some_transaction_id');

        $this->assertFalse($response->isCompleted());
    }

    public function testCanRefundTransactionPartially(): void
    {
        $gateway = $this->getGatewayForTransaction('transaction-settled');
        $response = $gateway->refundCharge('some_transaction_id', 1.00, false);

        $this->assertTrue($response->isCompleted());
    }

    public function testRefundTransactionPartiallyThrowsExceptionIfNotSettled(): void
    {
        $this->expectException(GatewayException::class);

        $gateway = $this->getGatewayForTransaction('transaction', false);
        $response = $gateway->refundCharge('some_transaction_id', 1.00, false);

        $this->assertFalse($response->isCompleted());
    }

    public function testGetSourceTokenUrlErrorResponse(): void
    {
        $response = $this->getGatewayForTransaction()->getSourceTokenUrl(PaymentMethod::factory()->create());

        $this->assertInstanceOf(ErrorResponse::class, $response);
    }

    public function testCreateSourceTokenThrowsExceptionIfNoMember(): void
    {
        $paymentMethod = PaymentMethod::factory()->create()->unsetRelation('member');
        $paymentMethod->member = null;

        $this->expectException(GatewayException::class);
        $this->expectExceptionMessage('Account required to setup payment method');

        $this->getGatewayForTransaction()->createSourceToken($paymentMethod);
    }

    public function testCreateSourceTokenCanVaultCard(): void
    {
        $paymentMethod = PaymentMethod::factory()->create();
        $paymentMethod->member->braintree_customer_id = $this->faker->uuid;

        $response = $this->getGatewayForPaymentMethod()->createSourceToken($paymentMethod);

        $this->assertTrue($response->isCompleted());
        $this->assertSame('1', $response->getResponse());
        $this->assertSame('424242******4242', $response->getCardNumber());
        $this->assertSame('0530', $response->getCardExpiry());
        $this->assertSame('05', $response->getCardExpiryMonth());
        $this->assertSame('2030', $response->getCardExpiryYear());
    }

    public function testCreateSourceTokenCanCreateBankAccount(): void
    {
        $paymentMethod = PaymentMethod::factory()->create();
        $paymentMethod->member->braintree_customer_id = $this->faker->uuid;

        $gateway = $this->getGatewayForPaymentMethod('us-bank-account');
        $gateway->request()->merge([
            'token' => [
                'type' => 'us_bank_account',
                'nonce' => $this->faker->uuid,
            ],
        ]);

        $response = $gateway->createSourceToken($paymentMethod);

        $this->assertTrue($response->isCompleted());
        $this->assertSame('1', $response->getResponse());
        $this->assertSame('0000', $response->getAchAccount());
        $this->assertSame('personal', $response->getAchEntity());
        $this->assertSame('011000015', $response->getAchRouting());
        $this->assertSame('checking', $response->getAchType());
    }

    public function testCanChargeSourceToken(): void
    {
        $paymentMethod = PaymentMethod::factory()->create();

        $response = $this->getGatewayForTransaction()->chargeSourceToken($paymentMethod, new Money(20.00), new SourceTokenChargeOptions);

        $this->assertTrue($response->isCompleted());
    }

    public function testChargesourceTokenCanThrowException(): void
    {
        $this->expectException(GatewayException::class);

        $paymentMethod = PaymentMethod::factory()->create();

        $this->getGatewayForTransaction('transaction', false)->chargeSourceToken($paymentMethod, new Money(20.00), new SourceTokenChargeOptions);
    }

    public function testCreateSourceTokenThrowsPaymentException(): void
    {
        $this->expectException(GatewayException::class);

        $paymentMethod = PaymentMethod::factory()->create();
        $paymentMethod->member->braintree_customer_id = $this->faker->uuid;

        $this->getGatewayForPaymentMethod('us-bank-account', false)->createSourceToken($paymentMethod);
    }

    public function testCanCreateCustomer(): void
    {
        $customerId = $this->getGatewayForCustomer()->createCustomer([
            'firstName' => $this->faker->firstName,
            'lastName' => $this->faker->lastName,
            'email' => $this->faker->email,
            'phone' => $this->faker->phoneNumber,
        ]);

        $this->assertSame('681486890', $customerId);
    }

    public function testCreateCustomerThrowsException(): void
    {
        $this->expectException(GatewayException::class);

        $this->getGatewayForCustomer('customer', false)->createCustomer([
            'firstName' => $this->faker->firstName,
            'lastName' => $this->faker->lastName,
            'email' => $this->faker->email,
            'phone' => $this->faker->phoneNumber,
        ]);
    }

    private function getGateway(): BraintreeGateway
    {
        /** @var \Ds\Domain\Commerce\Gateways\BraintreeGateway $gateway */
        $gateway = PaymentProvider::factory()->braintree()->create()->gateway;
        $gateway->request()->merge([
            'token' => [
                'nonce' => $this->faker->uuid,
            ], ]);

        return $gateway;
    }

    private function getGatewayForTransaction(string $fixture = 'transaction', bool $success = true): BraintreeGateway
    {
        $transaction = $this->mock(TransactionGateway::class);

        $transaction->shouldReceive('sale', 'refund', 'void')
            ->withAnyArgs()
            ->andReturn($this->getFixture($fixture, $success));

        $transaction->shouldReceive('find')
            ->withAnyArgs()
            ->andReturn($this->getFixture($success ? 'transaction-settled' : 'transaction', true, true));

        $this->app->bind(Gateway::class, function () use ($transaction) {
            $mock = $this->mock(Gateway::class);
            $mock->shouldReceive('transaction')->andReturn($transaction);

            return $mock;
        });

        return $this->getGateway();
    }

    private function getGatewayForCustomer(string $fixture = 'customer', bool $success = true): BraintreeGateway
    {
        $customer = $this->mock(CustomerGateway::class)
            ->shouldReceive('create')
            ->withAnyArgs()
            ->andReturn($this->getFixture($fixture, $success));

        $this->app->bind(Gateway::class, function () use ($customer) {
            $mock = $this->mock(Gateway::class);
            $mock->shouldReceive('customer')->andReturn($customer->getMock());

            return $mock;
        });

        return $this->getGateway();
    }

    private function getGatewayForPaymentMethod(string $fixture = 'credit-card', bool $success = true): BraintreeGateway
    {
        $paymentMethod = $this->mock(PaymentMethodGateway::class)
            ->shouldReceive('create')
            ->withAnyArgs()
            ->andReturn($this->getFixture($fixture, $success));

        $this->app->bind(Gateway::class, function () use ($paymentMethod) {
            $mock = $this->mock(Gateway::class);
            $mock->shouldReceive('paymentMethod')->andReturn($paymentMethod->getMock());

            return $mock;
        });

        return $this->getGateway();
    }

    private function getFixture(string $fixture, bool $success = true, bool $find = false): ?object
    {
        $mapping = [
            'credit-card' => [\Braintree\PaymentMethodGateway::class, \Braintree\PaymentMethod::class],
            'customer' => [\Braintree\CustomerGateway::class, \Braintree\Customer::class],
            'refund' => [\Braintree\TransactionGateway::class, \Braintree\Transaction::class],
            'transaction' => [\Braintree\TransactionGateway::class, \Braintree\Transaction::class],
            'transaction-settled' => [\Braintree\TransactionGateway::class, \Braintree\Transaction::class],
            'us-bank-account' => [\Braintree\PaymentMethodGateway::class, \Braintree\PaymentMethod::class],
        ];

        $response = \Braintree\Xml::buildArrayFromXml(File::get(base_path("tests/fixtures/braintree/{$fixture}.xml")));

        [$gatewayClassName, $objectClassName] = $mapping[$fixture];

        if ($find) {
            // this should never happen since an unsuccessful find will actually
            // result in a \Braintree\Exception\NotFound exception being thrown
            if ($success === false) {
                return null;
            }

            if ($objectClassName === \Braintree\PaymentMethod::class) {
                return \Braintree\PaymentMethodParser::parsePaymentMethod($response);
            }

            return $objectClassName::factory(array_values($response)[0]);
        }

        if ($success === false) {
            return new \Braintree\Result\Error(['errors' => [], 'message' => 'An error occurred']);
        }

        $gateway = (new \ReflectionClass($gatewayClassName))->newInstanceWithoutConstructor();

        return omniscient($gateway)->_verifyGatewayResponse($response);
    }
}
