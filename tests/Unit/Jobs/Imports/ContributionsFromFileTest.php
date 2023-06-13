<?php

namespace Tests\Unit\Jobs\Imports;

use Ds\Jobs\Import\ContributionsFromFile;
use Ds\Models\Import;
use Ds\Models\Product;
use Ds\Models\Variant;
use Exception;
use Tests\Concerns\InteractsWithImports;
use Tests\TestCase;

class ContributionsFromFileTest extends TestCase
{
    use InteractsWithImports;

    public function testGetColumnDefinitions(): void
    {
        $this->assertImportJobColumnDefinitions($this->app->make(ContributionsFromFile::class));
    }

    /**
     * @dataProvider creditCardLastFourValidatesProvider
     */
    public function testCreditCardLastFourValidates(bool $expectException, string $last4): void
    {
        $row = $this->generateImportRow(['cc_last_four' => $last4]);

        if ($expectException) {
            $this->expectException(Exception::class);
        }

        $this->validateImportRow($row);
        $this->expectNotToPerformAssertions();
    }

    public function creditCardLastFourValidatesProvider(): array
    {
        return [
            [true, '123'],
            [true, '12345'],
            [false, '1234'],
            [false, ''],
        ];
    }

    /**
     * @dataProvider creditCardExpiryValidatesProvider
     */
    public function testCreditCardExpiryValidates(bool $expectException, string $expiry): void
    {
        $row = $this->generateImportRow(['cc_expiry' => $expiry]);

        if ($expectException) {
            $this->expectException(Exception::class);
        }

        $this->validateImportRow($row);
        $this->expectNotToPerformAssertions();
    }

    public function creditCardExpiryValidatesProvider(): array
    {
        return [
            [true, '123'],
            [true, 'JUN21'],
            [true, '2106'],
            [false, '0621'],
            [false, ''],
        ];
    }

    private function generateImportRow(array $attributes = []): array
    {
        $variant = Variant::factory()->create();
        $product = Product::factory()->allowOutOfStock()->create();
        $product->variants()->save($variant);

        $row = array_fill_keys(
            $this->app->make(ContributionsFromFile::class)
                ->getColumnDefinitions()
                ->pluck('id')
                ->all(),
            null
        );

        $row['order_number'] = uuid();
        $row['order_local_time'] = now()->format('datetime');
        $row['item_product_code'] = $product->code;
        $row['item_variant_name'] = $variant->variantname;
        $row['billing_first_name'] = 'Randy';
        $row['billing_last_name'] = 'Marsh';

        return array_merge($row, $attributes);
    }

    private function validateImportRow(array $row): array
    {
        $importJob = $this->app->make(
            ContributionsFromFile::class,
            ['import' => new Import(['import_type' => 'ContributionsFromFile'])]
        );

        $data = array_values($row);

        return $importJob->validateRow($data);
    }
}
