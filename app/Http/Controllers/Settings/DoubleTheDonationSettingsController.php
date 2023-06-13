<?php

namespace Ds\Http\Controllers\Settings;

use Ds\Domain\DoubleTheDonation\DoubleTheDonationService;
use Ds\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DoubleTheDonationSettingsController extends Controller
{
    public function index(): View
    {
        return view('settings.integrations.double-the-donation');
    }

    public function store(): RedirectResponse
    {
        request()->merge([
            'double_the_donation_enabled' => request()->get('double_the_donation_enabled', 0),
        ]);

        sys_set();

        $this->flash->success('Settings saved successfully');

        return redirect()->back();
    }

    public function test(): RedirectResponse
    {
        request()->merge([
            'double_the_donation_enabled' => request()->get('double_the_donation_enabled', 0),
        ]);

        sys_set();

        try {
            app(DoubleTheDonationService::class)->test();
            $this->flash->success('Connection to Double the Donation tested successfully');
        } catch (Exception $e) {
            notifyException($e);
            $this->flash->error($e->getMessage());
        }

        return redirect()->back();
    }
}
