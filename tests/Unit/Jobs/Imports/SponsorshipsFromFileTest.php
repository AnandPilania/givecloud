<?php

namespace Tests\Unit\Jobs\Imports;

use Ds\Domain\Sponsorship\Models\Sponsorship as Sponsee;
use Ds\Jobs\Import\SponsorshipsFromFile;
use Illuminate\Support\Arr;
use Tests\Concerns\InteractsWithImports;
use Tests\TestCase;

class SponsorshipsFromFileTest extends TestCase
{
    use InteractsWithImports;

    public function testGetColumnDefinitions(): void
    {
        $this->assertImportJobColumnDefinitions($this->app->make(SponsorshipsFromFile::class));
    }

    public function testIsSponsoredAutoDefaultsToDisabledWhenImportingIsSponsored(): void
    {
        $row = $this->generateImportRow(['is_sponsored' => 'Y']);

        $this->app->make(SponsorshipsFromFile::class)->importRow($row);

        $sponsee = Sponsee::where('reference_number', $row['reference_number'])->firstOrFail();

        $this->assertFalse($sponsee->is_sponsored_auto);
    }

    public function testIsSponsoredAutoDefaultsToEnabledWhenNotImportingIsSponsored(): void
    {
        $row = $this->generateImportRow(['is_sponsored' => null]);

        $this->app->make(SponsorshipsFromFile::class)->importRow($row);

        $sponsee = Sponsee::where('reference_number', $row['reference_number'])->firstOrFail();

        $this->assertTrue($sponsee->is_sponsored_auto);
    }

    public function testImportingPaymentOptionGroup(): void
    {
        $row = $this->generateImportRow(['payment_option_group' => 'Monthly Food Aid']);

        $this->app->make(SponsorshipsFromFile::class)->importRow($row);

        $sponsee = Sponsee::where('reference_number', $row['reference_number'])->firstOrFail();

        $this->assertCount(1, $sponsee->paymentOptionGroups);
        $this->assertSame($row['payment_option_group'], $sponsee->paymentOptionGroup->name);
    }

    private function generateImportRow(array $attributes = []): array
    {
        $row = array_fill_keys(
            $this->app->make(SponsorshipsFromFile::class)
                ->getColumnDefinitions()
                ->pluck('id')
                ->all(),
            null
        );

        $row = array_merge($row, Arr::only(
            Sponsee::factory()->definition(),
            ['reference_number', 'first_name', 'last_name']
        ));

        return array_merge($row, $attributes);
    }
}
