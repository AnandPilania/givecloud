<?php

namespace Tests\Fakes;

use Ds\Services\ConfigService;

class FakeConfigService extends ConfigService
{
    /**
     * Boot the config.
     */
    public function boot()
    {
        $this->booted = true;
    }

    /**
     * Make sure data is loaded.
     *
     * @param $force
     */
    public function load($force = false)
    {
        $this->defaults = config('sys.defaults');

        $this->loaded = true;
    }

    /**
     * Save any changes done to the config data.
     */
    public function save(): bool
    {
        return true;
    }
}
