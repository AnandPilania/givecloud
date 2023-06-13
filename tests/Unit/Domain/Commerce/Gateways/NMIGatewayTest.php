<?php

namespace Tests\Unit\Domain\Commerce\Gateways;

use Closure;
use Ds\Domain\Commerce\Gateways\NMIGateway;
use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Illuminate\Http\Client\XmlParser;
use Ds\Models\Order;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NMIGatewayTest extends TestCase
{
    public function testCanParseTransactionResponse(): void
    {
        Http::fake([
            'nmi.com/*' => Http::fixture('nmi/transaction.xml'),
        ]);

        $transaction = $this->getGateway()->getTransaction('6349885977');

        $this->assertIsArray($transaction);
        $this->assertSame('4xxxxxxxxxxx1111', $transaction['cc_number'] ?? null);
        $this->assertSame('25.00', $transaction['actions'][0]['amount'] ?? null);
    }

    public function testCanParseCustomerVaultResponse(): void
    {
        Http::fake([
            'nmi.com/*' => Http::fixture('nmi/customer-vault.xml'),
        ]);

        $customerVault = $this->getGateway()->getCustomerVault('145651622');

        $this->assertIsArray($customerVault);
        $this->assertSame('4xxxxxxxxxxx1111', $customerVault['cc_number'] ?? null);
        $this->assertSame('123 Anywhere St', $customerVault['address_1'] ?? null);
    }

    public function testCanParseAccountUpdaterCustomerVaultsResponse(): void
    {
        Http::fake([
            'nmi.com/*' => Http::fixture('nmi/account-updater-customer-vaults.xml'),
        ]);

        $customerVaults = $this->getGateway()->getAccountUpdaterCustomerVaults();

        $this->assertCount(3, $customerVaults);
        $this->assertSame('4xxxxxxxxxxx1111', $customerVaults[0]['cc_number'] ?? null);
        $this->assertSame('20210527125044', $customerVaults[0]['account_updated'] ?? null);
    }

    /**
     * @dataProvider methodHandlesHttpErrorWithNullResponseProvider
     */
    public function testMethodHandlesHttpErrorWithNullResponse(string $methodName, array $arguments): void
    {
        Http::fake([
            'nmi.com/*' => Http::response(null, Response::HTTP_I_AM_A_TEAPOT),
        ]);

        $this->assertNull($this->getGateway()->{$methodName}(...$arguments));
    }

    public function methodHandlesHttpErrorWithNullResponseProvider(): array
    {
        return [
            ['getTransaction', ['bad_transaction_id']],
            ['getCustomerVault', ['bad_customer_vault_id']],
        ];
    }

    /**
     * @dataProvider methodHandlesHttpErrorWithEmptyCollectionResponseProvider
     */
    public function testMethodHandlesHttpErrorWithEmptyCollectionResponse(string $methodName): void
    {
        Http::fake([
            'nmi.com/*' => Http::response(null, Response::HTTP_I_AM_A_TEAPOT),
        ]);

        $this->assertCount(0, $this->getGateway()->{$methodName}());
    }

    public function methodHandlesHttpErrorWithEmptyCollectionResponseProvider(): array
    {
        return [
            ['getSettlements'],
            ['getAccountUpdaterCustomerVaults'],
        ];
    }

    public function testOrderIdDoesNotIncludeAuthAttemptsSuffix(): void
    {
        $order = Order::factory()->create(['auth_attempts' => 0]);

        $this->fakeHttpForPurchase(function ($data) use ($order) {
            $this->assertSame($order->client_uuid, $data->{'order-id'});
        });

        $this->getGateway()->getCaptureTokenUrl($order);
    }

    public function testOrderIdIncludesAuthAttemptsSuffix(): void
    {
        $order = Order::factory()->create(['auth_attempts' => 3]);

        $this->fakeHttpForPurchase(function ($data) use ($order) {
            $this->assertSame("{$order->client_uuid}_003", $data->{'order-id'});
        });

        $this->getGateway()->getCaptureTokenUrl($order);
    }

    private function getGateway(): NMIGateway
    {
        return $this->app->make(NMIGateway::class, [
            'provider' => PaymentProvider::factory()->nmi()->create(),
        ]);
    }

    private function fakeHttpForPurchase(Closure $callback = null)
    {
        Http::fake([
            'nmi.com/*' => function (Request $request) use ($callback) {
                if ($callback) {
                    $callback(json_decode(json_encode(
                        $this->app->make(XmlParser::class)($request->body())
                    )));
                }

                return Http::fixture('nmi/sale-step-1-completed.xml');
            },
        ]);
    }
}
