<?php

namespace Tests\Unit\Domain\Theming\Liquid;

use Ds\Domain\Theming\Liquid\Filters\LocaleFilters;
use Illuminate\Support\Collection;
use Tests\TestCase;

class LocaleFiltersTest extends TestCase
{
    public function testTranslatePluralization(): void
    {
        $localeFilters = new LocaleFilters(app('theme'));

        $this->assertEquals(
            $localeFilters->t('templates.fundraiser.donors_count'),
            'no donors'
        );

        $this->assertEquals(
            $localeFilters->t('templates.fundraiser.donors_count', ['count' => 0]),
            'no donors'
        );

        $this->assertEquals(
            $localeFilters->t('templates.fundraiser.donors_count', ['count' => []]),
            'no donors'
        );

        $this->assertEquals(
            $localeFilters->t('templates.fundraiser.donors_count', ['count' => 1]),
            '1 donor'
        );

        $this->assertEquals(
            $localeFilters->t('templates.fundraiser.donors_count', ['count' => new Collection(1)]),
            '1 donor'
        );

        $this->assertEquals(
            $localeFilters->t('templates.fundraiser.donors_count', ['count' => 12]),
            '12 donors'
        );

        $this->assertEquals(
            $localeFilters->t('templates.fundraiser.donors_count', ['count' => range(1, 12)]),
            '12 donors'
        );
    }
}
