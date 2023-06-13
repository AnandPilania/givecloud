<?php

namespace Ds\Http\Controllers\API\Settings;

use Ds\Domain\MissionControl\MissionControlService;
use Ds\Http\Controllers\API\Controller;
use Ds\Http\Resources\Settings\OrganizationResource;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function show(): OrganizationResource
    {
        user()->canOrRedirect('admin.advanced');

        return OrganizationResource::make();
    }

    public function store(Request $request): OrganizationResource
    {
        user()->canOrRedirect('admin.advanced');

        sys_set('clientName', $request->input('org_legal_name'));
        sys_set('org_legal_address', $request->input('org_legal_address'));
        sys_set('org_legal_number', $request->input('org_legal_number'));
        sys_set('org_legal_country', $request->input('org_legal_country'));
        sys_set('org_website', $request->input('org_website'));

        sys_set('locale', $request->input('locale'));
        sys_set('timezone', $request->input('timezone'));

        app(MissionControlService::class)->updateClient([
            'annual_fundraising_goal' => request('annual_fundraising_goal'),
            'market_category' => request('market_category'),
            'number_of_employees' => request('number_of_employees'),
        ]);

        return OrganizationResource::make();
    }
}
