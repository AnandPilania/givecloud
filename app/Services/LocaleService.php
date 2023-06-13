<?php

namespace Ds\Services;

class LocaleService
{
    private static string $locale;

    public function useSiteLocale(): void
    {
        static::$locale = app()->getLocale();

        app()->setLocale($this->siteLocale());
    }

    public function resetLocale(): void
    {
        app()->setLocale(static::$locale ?? config('app.locale'));
    }

    public function siteLocale(): string
    {
        return sys_get('locale') ?: config('app.fallback_locale');
    }
}
