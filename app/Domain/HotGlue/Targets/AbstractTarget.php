<?php

namespace Ds\Domain\HotGlue\Targets;

use Ds\Domain\HotGlue\HotGlue;
use Ds\Domain\Shared\Exceptions\MessageException;
use Illuminate\Support\Facades\Cache;

abstract class AbstractTarget
{
    public string $name;

    abstract public function listens(): array;

    public function isEnabled(): bool
    {
        return feature('hotglue_' . $this->name);
    }

    public function isLinked(): bool
    {
        return (bool) sys_get('bool:hotglue_' . $this->name . '_linked');
    }

    public function isConnected(): bool
    {
        $config = $this->config();

        $url = sprintf(
            '%s/%s/linkedTargets',
            data_get($config, 'flow_id'),
            sys_get('ds_account_name'),
        );

        $targetId = data_get($config, 'target_id');

        return Cache::remember(
            implode(':', ['hotglue', $this->name, 'connected']),
            now()->addDay(),
            function () use ($url, $targetId) {
                return app(HotGlue::class)
                    ->client()
                    ->get($url)
                    ->collect()
                    ->contains('target', $targetId);
            }
        );
    }

    public function config(): array
    {
        $config = config('services.hotglue.' . $this->name);

        if (! $config) {
            throw new MessageException('Config not implemented for target ' . $this->name);
        }

        return $config;
    }

    public function url(): string
    {
        $config = $this->config();

        $flowId = data_get($config, 'flow_id');

        return sprintf(
            '%s/%s/jobs',
            $flowId,
            sys_get('ds_account_name')
        );
    }
}
