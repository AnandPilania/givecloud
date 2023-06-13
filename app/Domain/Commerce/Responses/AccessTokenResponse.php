<?php

namespace Ds\Domain\Commerce\Responses;

use Ds\Domain\Commerce\Contracts\GatewayResponse;
use Illuminate\Support\Arr;

class AccessTokenResponse implements GatewayResponse
{
    /** @var array */
    protected $data;

    /**
     * Create an instance.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get the account id.
     *
     * @return string
     */
    public function getAccountId(): string
    {
        return (string) Arr::get($this->data, 'account_id');
    }

    /**
     * Get the access token.
     *
     * @return string
     */
    public function getAccessToken(): string
    {
        return (string) Arr::get($this->data, 'access_token');
    }

    /**
     * Get the token secret.
     *
     * @return string
     */
    public function getTokenSecret(): string
    {
        return (string) Arr::get($this->data, 'token_secret');
    }

    /**
     * Get the refresh token.
     *
     * @return string
     */
    public function getRefreshToken(): string
    {
        return (string) Arr::get($this->data, 'refresh_token');
    }

    /**
     * Get the publishable key.
     *
     * @return string
     */
    public function getPublishableKey(): string
    {
        return (string) Arr::get($this->data, 'publishable_key');
    }

    /**
     * Convert the response into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Convert the response instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }
}
