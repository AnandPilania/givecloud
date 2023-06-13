<?php

namespace Ds\Domain\Salesforce;

use Ds\Services\ConfigService;
use Illuminate\Config\Repository as Config;
use Omniphx\Forrest\Interfaces\StorageInterface;

class SalesforceTokenStorage implements StorageInterface
{
    protected string $path;

    private ConfigService $configService;

    public function __construct(ConfigService $configService, Config $config)
    {
        $this->configService = $configService;
        $this->path = $config->get('forrest.storage.path');
    }

    public function forget($key): void
    {
        $this->configService->forget($this->path . $key);
    }

    /**
     * @return mixed
     */
    public function get($key)
    {
        return  $this->configService->get('php:' . $this->path . $key);
    }

    public function has($key): bool
    {
        return $this->configService->has($this->path . $key);
    }

    public function put($key, $value): void
    {
        $this->configService->set('php:' . $this->path . $key, $value);
    }
}
