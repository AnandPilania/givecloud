<?php

namespace Ds\Domain\Commerce\Contracts;

use Ds\Domain\Commerce\Models\PaymentProvider;
use Illuminate\Http\Request;

interface Gateway
{
    /**
     * Get the gateway name.
     *
     * @return string
     */
    public function name(): string;

    /**
     * Get a display name for the gateway.
     *
     * @return string
     */
    public function getDisplayName(): string;

    /**
     * Get the website URL for the gateway.
     *
     * @return string|null
     */
    public function getWebsiteUrl(): ?string;

    /**
     * Check if gateway is in test mode.
     *
     * @return bool
     */
    public function isTestMode(): bool;

    /**
     * Get the provider.
     *
     * @return \Ds\Domain\Commerce\Models\PaymentProvider
     */
    public function provider(): PaymentProvider;

    /**
     * Get the config.
     *
     * @param string $key
     * @param bool $envConfig
     * @return mixed
     */
    public function config(string $key, bool $envConfig = true);

    /**
     * Get the request.
     *
     * @return \Illuminate\Http\Request
     */
    public function request(): Request;
}
