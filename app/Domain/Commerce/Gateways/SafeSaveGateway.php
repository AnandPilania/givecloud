<?php

namespace Ds\Domain\Commerce\Gateways;

class SafeSaveGateway extends NMIGateway
{
    /**
     * Get the gateway name.
     *
     * @return string
     */
    public function name(): string
    {
        return 'safesave';
    }

    /**
     * Get a display name for the gateway.
     *
     * @return string
     */
    public function getDisplayName(): string
    {
        return 'SafeSave Payment Services';
    }

    public function getWebsiteUrl(): ?string
    {
        return 'https://www.safesave-payments.com';
    }
}
