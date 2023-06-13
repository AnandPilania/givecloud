<?php

namespace Ds\Domain\HotGlue\Transformers\Salesforce;

use Ds\Models\Member;
use League\Fractal\TransformerAbstract;

class AccountTransformer extends TransformerAbstract
{
    public function transform(Member $account): array
    {
        return [
            'external_id' => [
                'name' => sys_get('salesforce_contact_external_id'),
                'value' => $account->hashid,
            ],
            'name' => $account->display_name,
            'first_name' => $account->first_name,
            'last_name' => $account->last_name,
            'type' => 'contact',
            'email' => $account->email,
            'title' => $account->title,
            'active' => true,
            'addresses' => $this->addresses($account),
            'photo_url' => $account->avatar,
            'department' => '',
            'company_name' => $account->bill_organization_name,
            'phone_numbers' => $this->phones($account),
        ];
    }

    protected function addresses(Member $account): array
    {
        return collect([[
            'line1' => $account->bill_address_01,
            'line2' => $account->bill_address_02,
            'line3' => null,
            'city' => $account->bill_city,
            'state' => $account->bill_state,
            'country' => $account->bill_country,
            'postal_code' => $account->bill_zip,
        ], [
            'line1' => $account->ship_address_01,
            'line2' => $account->ship_address_02,
            'line3' => null,
            'city' => $account->ship_city,
            'state' => $account->ship_state,
            'country' => $account->ship_country,
            'postal_code' => $account->ship_zip,
        ]])->filter(fn ($address) => (bool) $address['line1'])
            ->values()
            ->toArray();
    }

    protected function phones(Member $account): array
    {
        return collect([[
            'type' => 'primary',
            'number' => $account->bill_phone,
        ], [
            'type' => 'mobile',
            'number' => $account->ship_phone,
        ]])->filter(fn ($phone) => (bool) $phone['number'])
            ->values()
            ->toArray();
    }
}
