<?php

namespace Ds\Http\Resources\Settings;

use Ds\Illuminate\Http\Resources\Json\JsonResource;

class OrganizationResource extends JsonResource
{
    public function __construct()
    {
        $this->resource = new \stdClass;
    }

    public function toArray($request): array
    {
        return [
            'org_legal_name' => sys_get('org_legal_name') ?: sys_get('clientName'),
            'org_legal_address' => sys_get('org_legal_address'),
            'org_legal_number' => sys_get('org_legal_number'),
            'org_legal_country' => sys_get('org_legal_country') ?: sys_get('default_country'),
            'org_website' => sys_get('org_website'),

            'number_of_employees' => site()->client->number_of_employees,
            'market_category' => site()->client->market_category,
            'annual_fundraising_goal' => site()->client->annual_fundraising_goal,
            'locale' => sys_get('locale'),
            'timezone' => sys_get('timezone'),
        ];
    }
}
