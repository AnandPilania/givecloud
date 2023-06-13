<?php

namespace Tests\Unit\Domain\Commerce\Gateways;

use Ds\Domain\Commerce\Exceptions\PaymentException;
use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Domain\Commerce\Responses\TransactionResponse;
use Ds\Domain\Commerce\SourceTokenChargeOptions;
use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Models\Order;
use Ds\Models\PaymentMethod;
use Tests\TestCase;

class GivecloudTestGatewayTest extends TestCase
{
    /** @var \Ds\Domain\Commerce\Models\PaymentProvider */
    private $paymentProvider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paymentProvider = PaymentProvider::provider('givecloudtest')->firstOrFail();
    }

    public function testCaptureTokenUrl(): void
    {
        $res = $this->paymentProvider->getCaptureTokenUrl(new Order, 'https://example.com');

        $this->assertSame('https://example.com', (string) $res);
    }

    public function testSourceTokenUrl(): void
    {
        $res = $this->paymentProvider->getSourceTokenUrl(new PaymentMethod, 'https://example.com');

        $this->assertSame('https://example.com', (string) $res);
    }

    public function testChargeInvalidCaptureToken(): void
    {
        $this->expectException(MessageException::class);
        $this->expectExceptionMessage('The token provided is invalid.');

        $this->paymentProvider->chargeCaptureToken(new Order);
    }

    public function testChargeCaptureTokenDecline(): void
    {
        $this->expectException(PaymentException::class);

        $this->paymentProvider->gateway->request()->replace([
            'token' => $this->generateCardToken(['last4' => '9995']),
        ]);

        try {
            $this->paymentProvider->chargeCaptureToken(new Order);
        } catch (PaymentException $exception) {
            $res = $exception->getResponse();

            $this->assertInstanceOf(TransactionResponse::class, $res);
            $this->assertSame('The card has insufficient funds to complete the purchase.', $res->getResponseText());

            throw $exception;
        }
    }

    public function testChargeCaptureTokenApproved(): void
    {
        $this->paymentProvider->gateway->request()->replace([
            'token' => $this->generateBankAccountToken(),
        ]);

        $res = $this->paymentProvider->chargeCaptureToken(new Order);

        $this->assertInstanceOf(TransactionResponse::class, $res);
        $this->assertSame('APPROVED', $res->getResponseText());
    }

    public function testCreateSourceToken(): void
    {
        $this->paymentProvider->gateway->request()->replace([
            'token' => $this->generateCardToken(),
        ]);

        $res = $this->paymentProvider->createSourceToken(new PaymentMethod);

        $this->assertInstanceOf(TransactionResponse::class, $res);
        $this->assertSame('APPROVED', $res->getResponseText());
    }

    public function testChargeSourceToken(): void
    {
        $paymentMethod = PaymentMethod::factory()->create(['account_last_four' => '4242']);

        $res = $this->paymentProvider->chargeSourceToken($paymentMethod, money(12), new SourceTokenChargeOptions);

        $this->assertInstanceOf(TransactionResponse::class, $res);
        $this->assertSame('APPROVED', $res->getResponseText());
    }

    private function generateCardToken(array $requestData = []): string
    {
        $this->paymentProvider->gateway->request()->replace(array_merge([
            'type' => 'credit_card',
            'brand' => 'Visa',
            'last4' => '4242',
            'expiry' => '1225',
        ], $requestData));

        return $this->paymentProvider->gateway->getTokenId();
    }

    private function generateBankAccountToken(array $requestData = []): string
    {
        $this->paymentProvider->gateway->request()->replace(array_merge([
            'type' => 'bank_account',
            'last4' => '6789',
            'account_type' => 'checking',
            'account_holder_type' => 'individual',
            'routing_number' => '110000000',
        ], $requestData));

        return $this->paymentProvider->gateway->getTokenId();
    }
}
