<?php

namespace Ds\Http\Resources\DonationForms;

use Ds\Http\Resources\MediaResource;
use Ds\Illuminate\Http\Resources\Json\JsonResource;
use Ds\Models\Media;

class GlobalSettingsResource extends JsonResource
{
    public function __construct()
    {
        $this->resource = new \stdClass;
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'org_logo' => $this->getOrgLogo(),
            'org_primary_color' => sys_get('org_primary_color') ?: sys_get('default_color_1') ?: null,
            'org_legal_name' => sys_get('org_legal_name') ?: sys_get('clientName') ?: null,
            'org_legal_address' => sys_get('org_legal_address') ?: null,
            'org_legal_country' => sys_get('org_legal_country') ?: null,
            'org_legal_number' => sys_get('org_legal_number') ?: null,
            'org_check_mailing_address' => sys_get('org_check_mailing_address') ?: null,
            'org_support_number' => sys_get('org_support_number') ?: null,
            'org_support_email' => sys_get('org_support_email') ?: null,
            'org_other_ways_to_donate' => $this->getOtherWaysToDonate(),
            'org_privacy_officer_email' => sys_get('org_privacy_officer_email') ?: null,
            'org_privacy_policy_url' => sys_get('org_privacy_policy_url') ?: null,
            'org_website' => sys_get('org_website') ?: secure_site_url(),
        ];
    }

    private function getOrgLogo(): ?MediaResource
    {
        $media = Media::findByUrl(sys_get('org_logo')) ?? Media::findByUrl(sys_get('default_logo'));

        return $media ? MediaResource::make($media)->setThumb(['300x', 'crop' => 'entropy']) : null;
    }

    private function getOtherWaysToDonate(): array
    {
        $links = collect([
            ['label' => sys_get('org_faq_link_0_label'), 'href' => sys_get('org_faq_link_0_link')],
            ['label' => sys_get('org_faq_link_1_label'), 'href' => sys_get('org_faq_link_1_link')],
            ['label' => sys_get('org_faq_link_2_label'), 'href' => sys_get('org_faq_link_2_link')],
            ['label' => sys_get('org_faq_link_3_label'), 'href' => sys_get('org_faq_link_3_link')],
            ['label' => sys_get('org_faq_link_4_label'), 'href' => sys_get('org_faq_link_4_link')],
            ['label' => sys_get('org_faq_link_5_label'), 'href' => sys_get('org_faq_link_5_link')],
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
