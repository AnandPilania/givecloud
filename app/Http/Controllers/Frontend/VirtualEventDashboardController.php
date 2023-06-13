<?php

namespace Ds\Http\Controllers\Frontend;

class VirtualEventDashboardController extends Controller
{
    public function index($code)
    {
        return (new VirtualEventController)->index($code, 'dashboard');
    }
}
