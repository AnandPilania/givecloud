<?php

namespace Ds\Http\Controllers\Settings;

use Ds\Domain\HotGlue\HotGlue;
use Ds\Domain\Settings\Integrations\IntegrationSettingsService;
use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Http\Controllers\Controller;
use Illuminate\View\View;

class HotglueZeroConfigSettingsController extends Controller
{
    public function index(string $target): View
    {
        $integration = app(IntegrationSettingsService::class)
            ->getAll()
            ->first(fn ($i) => $i->id === $target);

        if (! $integration) {
            throw new MessageException('Integration does not exists.');
        }

        $resolved = app(HotGlue::class)->target($target);

        return view('settings.integrations.hotglue', [
            'integration' => $integration,
            'isConnected' => $resolved->isConnected(),
            'config' => app(HotGlue::class)->config($target),
        ]);
    }
}
