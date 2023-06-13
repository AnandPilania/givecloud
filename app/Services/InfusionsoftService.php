<?php

namespace Ds\Services;

use Ds\Common\Infusionsoft\Api;
use Ds\Models\GroupAccount;
use Ds\Models\Member as Account;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class InfusionsoftService
{
    /** @var \Ds\Common\Infusionsoft\Api */
    protected $api;

    /**
     * Create an instance.
     *
     * @param \Ds\Common\Infusionsoft\Api $api
     */
    public function __construct(Api $api)
    {
        $this->api = $api;
    }

    /**
     * Get API client.
     *
     * @return \Ds\Common\Infusionsoft\Api
     */
    public function getClient()
    {
        return $this->api;
    }

    /**
     * Get a Contact.
     *
     * @param int $contactId
     * @param array $optionalProperities
     * @return \stdClass|null
     */
    public function getContact($contactId, array $optionalProperities = [])
    {
        try {
            return $this->api->getContact($contactId, $optionalProperities);
        } catch (ModelNotFoundException $e) {
            return null;
        }
    }

    /**
     * Push an Account to Infusionsoft.
     *
     * @param \Ds\Models\Member $account
     * @return bool
     */
    public function pushAccount(Account $account)
    {
        if ($account->infusionsoft_contact_id) {
            return true;
        }

        $data = [
            'given_name' => $account->first_name,
            'family_name' => $account->last_name,
            'email_addresses' => [[
                'email' => $account->email ?? $account->bill_email,
                'field' => 'EMAIL1',
            ]],
            'opt_in_reason' => sys_get('infusionsoft_default_optin_reason'),
            'email_opted_in' => $account->email_opt_in,
            'addresses' => [[
                'field' => 'BILLING',
                'line1' => $account->bill_address_01,
                'line2' => $account->bill_address_02,
                'locality' => $account->bill_city,
                // 'region'       => $account->bill_state,
                'postal_code' => $account->bill_zip,
                // 'country_code' => $account->bill_country,
            ]],
            'phone_numbers' => [[
                'number' => $account->bill_phone,
                'field' => 'PHONE1',
            ]],
        ];

        if ($account->accountType && $account->accountType->is_organization) {
            $data['company'] = [
                'company_name' => $account->bill_organization_name,
            ];
        }

        $contact = $this->api->addContact($data, true);

        $account->infusionsoft_contact_id = $contact->id;
        $account->save();

        return true;
    }

    /**
     * Updates a Contact adding any group tags the to the Contact that
     * it does not already have.
     *
     * @param \Ds\Models\GroupAccount $groupAccount
     * @return bool
     */
    public function pushGroupAccount(GroupAccount $groupAccount)
    {
        $this->pushAccount($groupAccount->account);

        $tags = $groupAccount->group->metadata->infusionsoft_tags ?? [];

        if (empty($tags)) {
            return true;
        }

        return $this->addUniqueContactTags($groupAccount->account->infusionsoft_contact_id, $tags);
    }

    /**
     * Updates a Contact adding any tags the to the Contact that
     * it does not already have.
     *
     * @param int $contactId
     * @param array $tagIds
     * @return bool
     */
    public function addUniqueContactTags($contactId, array $tagIds)
    {
        try {
            $res = $this->api->getContactTags($contactId);
        } catch (ModelNotFoundException $e) {
            return false;
        }

        $currentTagIds = collect($res->tags)
            ->pluck('tag.id')
            ->values();

        $tagIds = collect($tagIds)
            ->diff($currentTagIds)
            ->unique()
            ->values();

        if ($tagIds->isNotEmpty()) {
            $this->api->addTags($contactId, $tagIds->all());
        }

        return true;
    }

    /**
     * Get list of all available tags.
     *
     * @return array
     */
    public function getTags()
    {
        return $this->api->tagsWhere([]);
    }

    /**
     * Get list of all available tags grouped by category.
     *
     * @return array
     */
    public function getTagsByCategory()
    {
        return collect($this->getTags())
            ->groupBy('category.name')
            ->toArray();
    }
}
