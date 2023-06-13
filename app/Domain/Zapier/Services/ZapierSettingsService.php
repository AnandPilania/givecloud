<?php

namespace Ds\Domain\Zapier\Services;

use Ds\Models\Passport\Client;
use Laravel\Passport\Passport;

class ZapierSettingsService
{
    /**
     * Enable or disable the user's zapier client.
     */
    public function set(bool $enableZapier): bool
    {
        if ($enableZapier === true) {
            return $this->clientExists()
                && sys_set('zapier_enabled', true);
        }

        return sys_set('zapier_enabled', false);
    }

    public function findClient(): ?Client
    {
        return Passport::client()
            ->where('name', Client::ZAPIER_CLIENT_NAME)
            ->whereKey(Client::ZAPIER_CLIENT_ID)
            ->first();
    }

    public function clientExists(): bool
    {
        return (bool) $this->findClient();
    }
}
