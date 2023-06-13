<?php

namespace Ds\Http\Controllers\Settings;

use Ds\Domain\Zapier\Services\ZapierSettingsService;
use Ds\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ZapierSettingsController extends Controller
{
    /** @var \Ds\Domain\Zapier\Services\ZapierSettingsService */
    private $zapierSettingsService;

    public function __construct(ZapierSettingsService $zapierSettingsService = null)
    {
        parent::__construct();

        $this->zapierSettingsService = $zapierSettingsService;
    }

    public function show(Request $request): View
    {
        user()->canOrRedirect('zapier');

        pageSetup('Zapier', 'jpanel');

        return view('settings.zapier', [
            '__menu' => 'admin.advanced',
            'currentDomain' => site()->subdomain,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        user()->canOrRedirect('zapier');

        $enableZapier = $request->has('enabled');
        $passportClient = $this->zapierSettingsService->set($enableZapier);

        if ($passportClient) {
            $this->flash->success(sprintf(
                'Zapier has been %s.',
                $enableZapier ? 'enabled' : 'disabled'
            ));
        } else {
            $this->flash->error(sprintf(
                'An error occured while %s Zapier.',
                $enableZapier ? 'enabling' : 'disabling'
            ));
        }

        return redirect()->back();
    }
}
