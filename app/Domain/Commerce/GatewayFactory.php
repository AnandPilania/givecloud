<?php

namespace Ds\Domain\Commerce;

use Ds\Domain\Commerce\Contracts\Gateway;
use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Domain\Shared\Exceptions\MessageException;
use Illuminate\Container\Container;

class GatewayFactory
{
    /** @var \Illuminate\Container\Container */
    protected $container;

    /**
     * Create an instance.
     *
     * @param \Illuminate\Container\Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Create a new Gateway instance.
     *
     * @param string $provider
     * @return \Ds\Domain\Commerce\Contracts\Gateway
     */
    public static function create(string $provider): Gateway
    {
        $paymentProvider = new PaymentProvider;
        $paymentProvider->provider = $provider;

        return app(GatewayFactory::class)->make($paymentProvider);
    }

    /**
     * Create a new Gateway instance.
     *
     * @param \Ds\Domain\Commerce\Models\PaymentProvider $paymentProvider
     * @return \Ds\Domain\Commerce\Contracts\Gateway
     */
    public function make(PaymentProvider $paymentProvider): Gateway
    {
        switch ($paymentProvider->provider) {
            case 'authorizenet':
                return $this->makeGateway($paymentProvider, Gateways\AuthorizeNetGateway::class);
            case 'braintree':
                return $this->makeGateway($paymentProvider, Gateways\BraintreeGateway::class);
            case 'caymangateway':
                return $this->makeGateway($paymentProvider, Gateways\CaymanGateway::class);
            case 'givecloudtest':
                return $this->makeGateway($paymentProvider, Gateways\GivecloudTestGateway::class);
            case 'gocardless':
                return $this->makeGateway($paymentProvider, Gateways\GoCardlessGateway::class);
            case 'nmi':
                return $this->makeGateway($paymentProvider, Gateways\NMIGateway::class);
            case 'offline':
                return $this->makeGateway($paymentProvider, Gateways\OfflineGateway::class);
            case 'paypalcheckout':
                return $this->makeGateway($paymentProvider, Gateways\PayPalCheckoutGateway::class);
            case 'paypalexpress':
                return $this->makeGateway($paymentProvider, Gateways\PayPalExpressGateway::class);
            case 'paysafe':
                return $this->makeGateway($paymentProvider, Gateways\PaysafeGateway::class);
            case 'safesave':
                return $this->makeGateway($paymentProvider, Gateways\SafeSaveGateway::class);
            case 'stripe':
                return $this->makeGateway($paymentProvider, Gateways\StripeGateway::class);
            case 'vanco':
                return $this->makeGateway($paymentProvider, Gateways\VancoGateway::class);
        }

        throw new MessageException("Provider [$paymentProvider->provider] not supported");
    }

    /**
     * Build a Gateway instance.
     */
    private function makeGateway(PaymentProvider $paymentProvider, string $klass)
    {
        return new $klass($paymentProvider, $this->container['config'], $this->container['request']);
    }
}
