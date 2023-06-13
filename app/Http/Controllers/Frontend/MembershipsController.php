<?php

namespace Ds\Http\Controllers\Frontend;

class MembershipsController extends Controller
{
    /**
     * Register controller middleware.
     */
    protected function registerMiddleware()
    {
        $this->middleware('auth.member');
        $this->middleware('requires.feature:membership');
    }

    public function index()
    {
        pageSetup(__('frontend/accounts.memberships.my_memberships'));

        return $this->renderTemplate('accounts/memberships');
    }
}
