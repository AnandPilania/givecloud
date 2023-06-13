<?php

namespace Tests\Concerns;

use Ds\Domain\Commerce\Gateways\StripeGateway;
use Ds\Domain\Commerce\Models\PaymentProvider;
use Illuminate\Support\Facades\File;
use function omniscient;
use function safe_explode;
use Stripe\Service\CoreServiceFactory;
use Stripe\StripeClient;
use Stripe\StripeObject;
use Stripe\Util\Util;
use Tests\MockeryExpectationProxy;
use Tests\MockeryMockProxy;

trait InteractsWithStripe
{
    /** @var \Mockery\MockInterface */
    private $stripeMock;

    protected function setUpInteractsWithStripe(): void
    {
        $getStripeFixture = fn (...$args) => $this->getStripeFixture(...$args);

        MockeryExpectationProxy::macro('andReturnStripe', function (string $name) use ($getStripeFixture) {
            return $this->andReturn($getStripeFixture($name));
        });

        $this->stripeMock = $this->partialMock(StripeClient::class);

        $this->app->bind(StripeClient::class, fn () => $this->stripeMock);
    }

    /** @return \Tests\MockeryMockProxy|\Tests\MockeryExpectationProxy */
    private function mockStripe(string $service)
    {
        [$serviceName, $expects] = safe_explode('->', $service, 2);

        $this->stripeMock->{$serviceName} ??= new MockeryMockProxy(
            $this->mock(omniscient(new CoreServiceFactory(null))->getServiceClass($serviceName))
        );

        if ($expects) {
            return $this->stripeMock->{$serviceName}->expects($expects);
        }

        return $this->stripeMock->{$serviceName};
    }

    private function getStripeGateway(): StripeGateway
    {
        return $this->app->make(StripeGateway::class, [
            'provider' => PaymentProvider::factory()->stripe()->create(),
        ]);
    }

    private static function getStripeFixture(string $fixture, $attributes = null): StripeObject
    {
        $resp = json_decode(File::get(base_path("tests/fixtures/stripe/{$fixture}.json")), true);

        return Util::convertToStripeObject(array_merge($resp, $attributes ?? []), null);
    }
}
