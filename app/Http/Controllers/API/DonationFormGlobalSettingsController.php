<?php

namespace Ds\Http\Controllers\API;

use Ds\Http\Requests\API\DonationFormGlobalSettingsUpdateFormRequest;
use Ds\Http\Resources\Settings\GlobalSettingsResource;

class DonationFormGlobalSettingsController extends Controller
{
    public function show(): GlobalSettingsResource
    {
        user()->canOrRedirect(['product.view']);

        return GlobalSettingsResource::make();
    }

    public function store(DonationFormGlobalSettingsUpdateFormRequest $request): GlobalSettingsResource
    {
        sys_set('org_legal_name', $request->input('org_legal_name'));
        sys_set('org_legal_address', $request->input('org_legal_address'));
        sys_set('org_legal_country', $request->input('org_legal_country'));
        sys_set('org_legal_number', $request->input('org_legal_number'));
        sys_set('org_check_mailing_address', $request->input('org_check_mailing_address'));
        sys_set('org_support_number', $request->input('org_support_number'));
        sys_set('org_support_email', $request->input('org_support_email'));
        sys_set('json:org_other_ways_to_donate', $request->input('org_other_ways_to_donate'));
        sys_set('org_privacy_officer_email', $request->input('privacy_officer_email'));
        sys_set('org_privacy_policy_url', $request->input('privacy_policy_url'));

        return GlobalSettingsResource::make();
    }
}
