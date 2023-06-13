<?php

namespace Ds\Http\Controllers\API\Settings;

use Ds\Http\Controllers\API\Controller;
use Ds\Http\Resources\Settings\FundraisingResource;
use Illuminate\Http\Request;

class FundraisingController extends Controller
{
    public function show(): FundraisingResource
    {
        user()->canOrRedirect('admin.advanced');

        return FundraisingResource::make();
    }

    public function store(Request $request): FundraisingResource
    {
        user()->canOrRedirect('admin.advanced');

        sys_set('org_check_mailing_address', $request->input('org_check_mailing_address'));
        sys_set('org_support_number', $request->input('org_support_number'));
        sys_set('org_support_number_country_code', $request->input('org_support_number_country_code'));
        sys_set('org_support_email', $request->input('org_support_email'));

        sys_set('org_privacy_officer_email', $request->input('org_privacy_officer_email'));
        sys_set('org_privacy_policy_url', $request->input('org_privacy_policy_url'));
        sys_set('org_faq_alternative_question', $request->input('org_faq_alternative_question'));
        sys_set('org_faq_alternative_answer', $request->input('org_faq_alternative_answer'));
        // TODO 'tax_receipt' => false,

        foreach (range(0, 5) as $i) {
            sys_set('org_faq_link_' . $i . '_label', data_get($request->input('org_other_ways_to_donate'), $i . '.label'));
            sys_set('org_faq_link_' . $i . '_link', data_get($request->input('org_other_ways_to_donate'), $i . '.href'));
        }

        return FundraisingResource::make();
    }
}
