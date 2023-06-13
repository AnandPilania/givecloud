<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;
use Illuminate\Support\Str;

class SettingsDrop extends Drop
{
    const SOURCE_REQUIRED = false;

    /**
     * Catch all method that is invoked before a specific method
     *
     * @param string $method
     * @return mixed
     */
    protected function liquidMethodMissing($method)
    {
        if ($method === 'google_maps_api_key') {
            return volt_setting('google_maps_api_key') ?: config('services.google-maps.api_key');
        }

        if (Str::contains($method, ':')) {
            $settings = [];

            foreach (explode(':', $method) as $key) {
                $settings[] = volt_setting($key);
            }

            return $settings;
        }

        return volt_setting($method);
    }
}
