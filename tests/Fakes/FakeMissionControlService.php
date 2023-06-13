<?php

namespace Tests\Fakes;

use Ds\Domain\MissionControl\MissionControlService;
use Ds\Domain\MissionControl\Models\Site;
use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Models\User;
use Illuminate\Support\Collection;
use Swift_Message;

class FakeMissionControlService extends MissionControlService
{
    /**
     * Get the site.
     *
     * @return \Ds\Domain\MissionControl\Models\Site
     */
    public function getSite(): Site
    {
        return reqcache('sys-backend:site', function () {
            $dsAccountName = sys_get('ds_account_name');

            return new Site([
                'id' => 1,
                'status' => 'ACTIVE',
                'ds_account_name' => $dsAccountName,
                'db_connection' => 'testing',
                'db_name' => $dsAccountName,
                'name' => "ACME ($dsAccountName)",
                'short_name' => 'ACME',
                'version' => 'feature-affinity',
                'organization' => 'ACME',
                'txn_fee' => 0.0125,
                'txn_fee_currency' => 'USD',
                'created_at' => '2020-01-01 00:00:00',
                'client' => [
                    'id' => 1,
                    'status' => 'ACTIVE',
                    'name' => 'ACME Corporation',
                    'city' => 'Miami',
                    'province' => 'FL',
                    'country' => 'US',
                    'customer_id' => '1mk51ePQSXpV7wDTW',
                    'hubspot_company_id' => null,
                    'direct_billing_enabled' => 1,
                    'number_of_employees' => '1-5',
                    'annual_fundraising_goal' => '$750 to $1.25M',
                    'market_category' => 'Animal & Wildlife > Museums, Zoos & Aquariums',
                ],
                'partner' => (object) [
                    'id' => 1,
                    'identifier' => 'gc',
                    'name' => 'Givecloud',
                    'in_app_brand' => null,
                    'in_app_brand_phrase' => null,
                    'in_app_brand_phrase_url' => null,
                    'in_app_logo' => null,
                    'in_app_logo_light' => null,
                ],
                'domains' => [],
                'subscription' => [
                    'id' => 1,
                    'status' => 'active',
                    'amount' => 69,
                    'currency' => 'USD',
                    'transaction_fee' => 0.0125,
                    'mrr' => 69,
                    'interval' => 'month',
                    'trial_ends_on' => null,
                    'support_chat' => 'standard',
                    'support_phone' => 'none',
                    'purchased_date' => '2020-01-01 00:00:00',
                ],
                'plan' => [
                    'id' => 1,
                    'name' => 'UNLIMITED',
                ],
            ]);
        });
    }

    /**
     * Get the authoritative site.
     *
     * @return \Ds\Domain\MissionControl\Models\Site|null
     */
    public function getAuthoritativeSite(): ?Site
    {
        $dsAccountName = sys_get('sponsorship_database_name');

        if (empty($dsAccountName)) {
            return null;
        }

        throw new MessageException('Not setup for fake authoritative sites yet.');
    }

    /**
     * Check if domain is already in use by another site.
     *
     * @return bool
     */
    public function isDomainAlreadyInUse($domain): bool
    {
        return false;
    }

    public function isBlockedIp(?string $ip = null, array $lists = null): bool
    {
        return false;
    }

    /**
     * Get in app updates.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getInAppUpdates(): Collection
    {
        return collect();
    }

    /**
     * Get notices.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getNotices(): Collection
    {
        return collect();
    }

    /**
     * Get invoices.
     *
     * @return object|null
     */
    public function getInvoice($invoiceId)
    {
        return null;
    }

    public function getPlans(): Collection
    {
        return collect([]);
    }

    /**
     * Updating the site custom domains.
     *
     * @param array $domains
     */
    public function updateCustomDomains(array $domains)
    {
        // do nothing
    }

    /**
     * Updating the client.
     *
     * @param array $attributes
     */
    public function updateClient(array $attributes)
    {
        // no nothing
    }

    public function setSubscription(array $attributes): bool
    {
        return true;
    }

    public function updateQuickStartTask(string $slug, array $attributes): void
    {
        // Nope.
    }

    public function updateResellerId(int $resellerId = 1): void
    {
        // Fake
    }

    public function updateBilling(array $attributes): void
    {
        // Nope
    }

    public function updateClientNotes(array $attributes): void
    {
        // Nada
    }

    public function updateInvoices(array $attributes): void
    {
        // Rien
    }

    public function updateContacts(array $attributes): void
    {
        // Niet
    }

    public function updateSubscriptions(array $attributes): void
    {
        // Zip
    }

    public function updateTransactionFeeInvoices(array $attributes): void
    {
        // No go
    }

    /**
     * Adds a note on the client.
     *
     * @param string $body
     */
    public function addNote($body)
    {
        // do nothing
    }

    /**
     * Logs a Swift Message.
     */
    public function logSwiftMessage(Swift_Message $message, string $smtpTransactionLog, int $sent): void
    {
        // do nothing
    }

    public function updateSite(array $attributes): void
    {
        // Do nothing.
    }

    public function updateSiteUsers(User $user): void
    {
        // Do nothing.
    }
}
