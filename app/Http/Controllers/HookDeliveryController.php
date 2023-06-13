<?php

namespace Ds\Http\Controllers;

use Ds\Models\HookDelivery;
use Illuminate\Contracts\View\View;

class HookDeliveryController extends Controller
{
    public function show(HookDelivery $delivery): View
    {
        user()->canOrRedirect('hooks');

        return view('hooks._delivery', compact('delivery'));
    }
}
