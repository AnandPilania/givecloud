<?php

namespace Tests\Unit\Services;

use Ds\Services\LocaleService;
use Tests\TestCase;

class LocaleServiceTest extends TestCase
{
    public function testSiteLocaleReturnsFallback(): void
    {
        sys_set('locale', null);

        $locale = \Config::get('app.fallback_locale');

        $this->assertSame($locale, $this->app->make(LocaleService::class)->siteLocale());
    }

    public function testSiteLocaleReturnsLocaleWhenSet(): void
    {
        sys_set('locale', 'fr-CA');

        $this->assertSame('fr-CA', $this->app->make(LocaleService::class)->siteLocale());
    }

    public function testUseSiteLocaleSetsLocale(): void
    {
        sys_set('locale', 'fr-CA');

        $this->app->make(LocaleService::class)->useSiteLocale();

        $this->assertSame('fr-CA', $this->app->getLocale());
    }

    public function testResetSiteLocaleSetsFallback(): void
    {
        $fallback = \Config::get('app.fallback_locale');

        sys_set('locale', 'fr-CA');

        $this->app->make(LocaleService::class)->resetLocale();

        $this->assertSame($fallback, $this->app->getLocale());
    }

    public function testResetSiteLocaleSetsInitialLocale(): void
    {
        sys_set('locale', 'fr-CA');

        $this->assertSame('en-US', $this->app->getLocale());

        $this->app->make(LocaleService::class)->useSiteLocale();

        $this->assertSame('fr-CA', $this->app->getLocale());

        $this->app->make(LocaleService::class)->resetLocale();

        $this->assertSame('en-US', $this->app->getLocale());
    }
}
