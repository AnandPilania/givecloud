<?php

namespace Ds\Http\Controllers\API\Settings;

use Ds\Http\Controllers\API\Controller;
use Ds\Http\Resources\Settings\BrandingResource;
use Ds\Models\Media;
use Illuminate\Http\Request;

class BrandingController extends Controller
{
    public function show(): BrandingResource
    {
        user()->canOrRedirect('admin.advanced');

        return BrandingResource::make();
    }

    public function store(Request $request): BrandingResource
    {
        user()->canOrRedirect('admin.advanced');

        sys_set('org_logo', Media::find($request->input('org_logo')));
        sys_set('org_primary_color', $request->input('org_primary_color'));

        cache()->tags('theming')->flush();
        cache()->tags('theming')->forever('settings_updated', now()->toApiFormat());

        return BrandingResource::make();
    }
}
