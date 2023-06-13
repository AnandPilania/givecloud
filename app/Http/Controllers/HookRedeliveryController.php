<?php

namespace Ds\Http\Controllers;

use Ds\Domain\Webhook\Services\HookService;
use Ds\Models\HookDelivery;
use Illuminate\Contracts\View\View;

class HookRedeliveryController extends Controller
{
    /** @var \Ds\Domain\Webhook\Services\HookService */
    protected $hookService;

    public function __construct(HookService $hookService)
    {
        parent::__construct();

        $this->hookService = $hookService;
    }

    public function store(HookDelivery $delivery): View
    {
        user()->canOrRedirect('hooks');

        $this->hookService->redeliver($delivery);

        return view('hooks._delivery', compact('delivery'));
    }
}
