<?php

namespace Tests\Unit\Domain\Commerce\Gateways;

use Ds\Domain\Commerce\Gateways\VancoGateway;
use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Domain\Commerce\Responses\TransactionResponse;
use Ds\Domain\Commerce\SourceTokenChargeOptions;
use Ds\Models\PaymentMethod;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VancoGatewayTest extends TestCase
{
    public function testCanChargeSourceToken(): void
    {
        Cache::flush();

        Http::fake([
            'vancopayments.com/*' => Http::sequence()
                ->pushResponse(Http::fixture('vanco/nvp/login.txt'))
                ->pushResponse(Http::fixture('vanco/nvp/charge-source-token.txt')),
        ]);

        $paymentMethod = PaymentMethod::factory()->creditCard()->create([
            'account_type' => 'Visa',
            'account_last_four' => '1111',
        ]);

        $res = $this->getGateway()->chargeSourceToken($paymentMethod, money(25), new SourceTokenChargeOptions);

        $this->assertInstanceOf(TransactionResponse::class, $res);
        $this->assertSame('200420305', $res->getTransactionId());
        $this->assertSame('4*** **** **** 1111', $res->getCardNumber());
        $this->assertSame('AP', $res->getResponseText());
    }

    public function testCanParseTransactionResponse(): void
    {
        Cache::flush();

        Http::fake([
            'vancopayments.com/*' => Http::sequence()
                ->pushResponse(Http::fixture('vanco/login.xml'))
                ->pushResponse(Http::fixture('vanco/transaction.xml')),
        ]);

        $transaction = $this->getGateway()->getTransaction('200420305');

        $this->assertIsArray($transaction);
        $this->assertSame('1', Arr::get($transaction, 'TransactionCount'));
        $this->assertSame('Approval', Arr::get($transaction, 'Transactions.Transaction.CCAuthDesc'));
        $this->assertSame('25.00', Arr::get($transaction, 'Transactions.Transaction.Amount'));
    }

    private function getGateway(): VancoGateway
    {
        return $this->app->make(VancoGateway::class, [
            'provider' => PaymentProvider::factory()->vanco()->create(),
        ]);
    }
}
