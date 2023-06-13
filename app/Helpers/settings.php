<?php

/**
 * Get or set theme setting value(s).
 *
 * @param array|string|null $name
 *
 * @see Ds\Domain\Theming\Theme
 *
 * @return \Illuminate\Support\Collection|mixed
 */
function setting($name = null)
{
    return app('theme')->setting($name);
}

if (defined('APP_LEVEL_ENABLED')) {
    function sys_get(?string $key = null, $default = null)
    {
        return $key === null ? optional() : null;
    }
}

if (! function_exists('sys_get')) {
    /**
     * Get a config value or the config service.
     *
     * @param string|null $key
     * @param mixed $default
     *
     * @see \Ds\Facades\SysConfig
     * @see \Ds\Services\ConfigService
     *
     * @return \Ds\Services\ConfigService|mixed
     */
    function sys_get(?string $key = null, $default = null)
    {
        $config = \Ds\Services\ConfigService::getInstance();

        if ($key === null) {
            return $config;
        }

        return $config->get($key, $default);
    }
}

/**
 * Set config value(s).
 *
 * @param array|string|null $key
 * @param mixed $value
 *
 * @see \Ds\Facades\SysConfig
 * @see \Ds\Services\ConfigService
 *
 * @return bool
 */
function sys_set($key = null, $value = null): bool
{
    if ($key === null) {
        return sys_get()->setFromRequest();
    }

    return sys_get()->set($key, $value);
}
