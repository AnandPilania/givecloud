<?php

namespace Ds\Domain\Commerce\Contracts;

use Ds\Domain\Commerce\Responses\AccessTokenResponse;

interface OAuth
{
    /**
     * Create an authentication URL.
     *
     * @param string $state
     * @param string|null $returnUrl
     * @return string
     */
    public function getAuthenticationUrl(string $state, ?string $returnUrl = null): string;

    /**
     * Get permanent access token which allows use of the API on behalf of the user.
     *
     * @param string|null $returnUrl
     * @return \Ds\Domain\Commerce\Responses\AccessTokenResponse
     */
    public function getAccessToken(?string $returnUrl = null): AccessTokenResponse;

    /**
     * Verify the access token works.
     *
     * @return bool
     */
    public function verifyAccessToken(): bool;
}
