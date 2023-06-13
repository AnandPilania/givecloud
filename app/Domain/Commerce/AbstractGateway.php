<?php

namespace Ds\Domain\Commerce;

use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Domain\Commerce\Responses\TransactionResponse;
use Illuminate\Config\Repository as Config;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

abstract class AbstractGateway
{
    /** @var \Ds\Domain\Commerce\Models\PaymentProvider */
    protected $provider;

    /** @var \Illuminate\Config\Repository */
    protected $config;

    /** @var string */
    protected $configKey;

    /** @var \Illuminate\Http\Request */
    protected $request;

    /** @var bool */
    protected $configureForKioskApp = false;

    /**
     * Create an instance.
     */
    public function __construct(PaymentProvider $provider, Config $config, Request $request)
    {
        $this->provider = $provider;
        $this->config = $config;
        $this->request = $request;
    }

    abstract public function name(): string;

    abstract public function getDisplayName(): string;

    public function getWebsiteUrl(): ?string
    {
        return null;
    }

    /**
     * Check if gateway is in test mode.
     *
     * @return bool
     */
    public function isTestMode(): bool
    {
        return (bool) Arr::get($this->provider()->getAttributes(), 'test_mode', false);
    }

    /**
     * Get the provider.
     *
     * @return \Ds\Domain\Commerce\Models\PaymentProvider
     */
    public function provider(): PaymentProvider
    {
        return $this->provider;
    }

    /**
     * Get the config.
     *
     * @param string $key
     * @param bool $envConfig
     * @return mixed
     */
    public function config(string $key, bool $envConfig = true)
    {
        $provider = $this->provider();

        // check the site settings for the currency
        // in the future when/if multi-currency support is
        // added this can be removed as currency would be
        // stored as a configuration on the payment provider
        if ($key === 'currency') {
            return sys_get('dpo_currency');
        }

        // use the method defined in the gateway
        if ($key === 'test_mode') {
            return $this->isTestMode();
        }

        // check the payment provider attributes
        if (array_key_exists($key, $provider->getAttributes()) || $provider->hasGetMutator($key)) {
            return $provider->getAttribute($key);
        }

        // check the payment provider config
        if (Arr::has($provider->config, $key)) {
            return Arr::get($provider->config, $key);
        }

        // check the gateways config
        if ($this->configKey) {
            $prefix = "gateways.{$this->configKey}";

            if ($envConfig) {
                $prefix .= '.' . ($this->config('test_mode') ? 'test' : 'prod');
            }

            return $this->config->get("$prefix.$key");
        }

        return null;
    }

    public function configureForKioskApp(bool $configureForKioskApp = true): void
    {
        $this->configureForKioskApp = $configureForKioskApp;
    }

    /**
     * Get the request.
     *
     * @return \Illuminate\Http\Request
     */
    public function request(): Request
    {
        return $this->request;
    }

    /**
     * Get the client IP.
     *
     * @return string
     */
    public function getClientIp()
    {
        $ip = $this->request()->ip();

        // ignore localhost requests (artisan commands, etc...)
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return '';
        }

        return $ip;
    }

    /**
     * Create a transaction response.
     *
     * @param array $data
     * @return \Ds\Domain\Commerce\Responses\TransactionResponse
     */
    protected function createTransactionResponse(array $data): TransactionResponse
    {
        if (! array_key_exists('ip_address', $data)) {
            $data['ip_address'] = $this->getClientIp();
        }

        return new TransactionResponse($this->provider(), $data);
    }

    /**
     * Get view data for the gateway.
     *
     * @return string
     */
    public function getView(): string
    {
        $output = '';
        $config = optional($this)->getViewConfig();

        if (! empty($config->scripts)) {
            $output .= collect($config->scripts)
                ->map(function ($script) {
                    $attributes = collect((array) $script)->map(function ($value, $key) {
                        if ($key === 0) {
                            $key = 'src';
                        }

                        if ($value === true) {
                            return $key;
                        }

                        return "$key=\"$value\"";
                    })->implode(' ');

                    return "<script $attributes></script>";
                })->implode("\n") . "\n";
        }

        if (! empty($config->settings)) {
            $output .= sprintf(
                "<script>Givecloud.Gateway('%s').setConfig(%s);</script>\n",
                $this->name(),
                json_encode($config->settings),
            );
        }

        return trim($output);
    }

    /**
     * Get view configuration for the gateway.
     *
     * @return object|null
     */
    public function getViewConfig(): ?object
    {
        return null;
    }
}
