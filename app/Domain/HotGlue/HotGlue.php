<?php

namespace Ds\Domain\HotGlue;

use Ds\Domain\HotGlue\Targets\AbstractTarget;
use Ds\Domain\HotGlue\Targets\HubSpotTarget;
use Ds\Domain\HotGlue\Targets\MailchimpTarget;
use Ds\Domain\HotGlue\Targets\SalesforceTarget;
use Illuminate\Http\Client\PendingRequest;

class HotGlue
{
    public const TARGETS = [
        HubSpotTarget::class,
        SalesforceTarget::class,
        MailchimpTarget::class,
    ];

    public function config(string $target): array
    {
        $config = $this->target($target)->config();

        return [
            'apiKey' => config('services.hotglue.api_key'),
            'envId' => config('services.hotglue.env_id'),
            'flowId' => data_get($config, 'flow_id'),
            'routes' => [
                'connect' => route('api.settings.hotglue.connect'),
                'disconnect' => route('api.settings.hotglue.disconnect'),
            ],
            'target' => [
                'name' => $target,
                'id' => data_get($config, 'target_id'),
            ],
        ];
    }

    public function target(string $name): AbstractTarget
    {
        return collect(self::TARGETS)
            ->map(fn (string $target) => app($target))
            ->firstOrFail(fn (AbstractTarget $target) => $target->name === $name);
    }

    public function client(): PendingRequest
    {
        return app(HotGlueClient::class)->client();
    }
}
