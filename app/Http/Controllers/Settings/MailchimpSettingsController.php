<?php

namespace Ds\Http\Controllers\Settings;

use Ds\Domain\HotGlue\HotGlue;
use Ds\Domain\HotGlue\Jobs\Mailchimp\SyncSupportersJob;
use Ds\Http\Controllers\Controller;
use Ds\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MailchimpSettingsController extends Controller
{
    public function index(): View
    {
        $resolved = app(HotGlue::class)->target('mailchimp');

        return view('settings.integrations.mailchimp', [
            'isConnected' => $resolved->isConnected(),
            'config' => app(HotGlue::class)->config('mailchimp'),
        ]);
    }

    public function sync(): RedirectResponse
    {
        Member::query()->active()->whereNotNull('email')->chunk(500, function ($chunk) {
            SyncSupportersJob::dispatch($chunk);
        });

        $this->flash->success('Supporters sync job queued successfully');

        return redirect()->back();
    }
}
