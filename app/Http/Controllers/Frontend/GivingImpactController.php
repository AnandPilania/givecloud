<?php

namespace Ds\Http\Controllers\Frontend;

class GivingImpactController extends Controller
{
    /**
     * Register controller middleware.
     */
    protected function registerMiddleware()
    {
        $this->middleware('auth.member');
    }

    public function index()
    {
        pageSetup(__('frontend/accounts.giving_impact.my_impact'));

        return $this->renderTemplate('accounts/giving-impact');
    }
}
