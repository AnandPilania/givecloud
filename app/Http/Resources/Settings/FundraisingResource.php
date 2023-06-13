<?php

namespace Ds\Http\Resources\Settings;

use Ds\Illuminate\Http\Resources\Json\JsonResource;

class FundraisingResource extends JsonResource
{
    public function __construct()
    {
        $this->resource = new \stdClass;
    }

    public function toArray($request): array
    {
        return [
            'org_support_number' => sys_get('org_support_number'),
            'org_support_number_country_code' => sys_get('org_support_number_country_code') ?: sys_get('org_legal_country') ?: sys_get('default_country'),
            'org_support_email' => sys_get('org_support_email'),
            'org_other_ways_to_donate' => $this->getOtherWaysToDonate(),
            'org_faq_alternative_question' => sys_get('org_faq_alternative_question'),
            'org_faq_alternative_answer' => sys_get('org_faq_alternative_answer'),
            'org_check_mailing_address' => sys_get('org_check_mailing_address'),
            'org_privacy_officer_email' => sys_get('org_privacy_officer_email'),
            'org_privacy_policy_url' => sys_get('org_privacy_policy_url'),
            // TODO 'tax_receipt' => false,
        ];
    }

    private function getOtherWaysToDonate(): array
    {
        $links = collect([
            ['id' => 1, 'label' => sys_get('org_faq_link_0_label'), 'href' => sys_get('org_faq_link_0_link')],
            ['id' => 2, 'label' => sys_get('org_faq_link_1_label'), 'href' => sys_get('org_faq_link_1_link')],
            ['id' => 3, 'label' => sys_get('org_faq_link_2_label'), 'href' => sys_get('org_faq_link_2_link')],
            ['id' => 4, 'label' => sys_get('org_faq_link_3_label'), 'href' => sys_get('org_faq_link_3_link')],
            ['id' => 5, 'label' => sys_get('org_faq_link_4_label'), 'href' => sys_get('org_faq_link_4_link')],
            ['id' => 6, 'label' => sys_get('org_faq_link_5_label'), 'href' => sys_get('org_faq_link_5_link')],
        ])->reject(function ($link) {
            return empty($link['label']) || empty($link['href']);
        })->all();

        // settings was originally stored as a JSON setting but was split
        // into multiple settings when added to the organization settings screen
        if (empty($links) && sys_get('org_other_ways_to_donate')) {
            return sys_get('json:org_other_ways_to_donate');
        }

        return $links;
    }
}
