<?php

namespace Tests\Unit\Domain\Commerce;

use Tests\TestCase;

class NumeralTest extends TestCase
{
    /**
     * @dataProvider parsingStringsIntoFloatsProvider
     */
    public function testParsingStringsIntoFloats(?float $expected, string $value): void
    {
        $this->assertSame($expected, numeral($value)->toFloat());
    }

    public function parsingStringsIntoFloatsProvider(): array
    {
        return [
            [null, ''],
            [null, 'not-a-number'],
            [100.5, '100.500000'],
            [1000.5, '1,000.5'],
            [1000.5, '$1,000.5'],
        ];
    }

    public function testUsesCurrencyFormatAsDefault(): void
    {
        $this->assertSame('1,000.00', (string) numeral(1000));
    }

    /**
     * @dataProvider formattingProvider
     */
    public function testFormatting(string $expected, $value, string $format, array $conditions = []): void
    {
        $this->assertSame($expected, numeral($value)->format($format, $conditions));
    }

    public function formattingProvider(): array
    {
        return [
            ['00100', 100.1234, '00000'],
            ['-12', -12, '0'],
            ['1,000', 1000, '0,0[.]00'],
            ['1,000.00', 1000, '0,0.00'],
            ['1,000,000', 1000000, '0,0'],
            ['1m', 1000000, '0[.]0a'],
            ['1.4m', 1360000, '0[.]0a'],
            ['1,360k', 1360000, '0,0ak'],
            ['1m', 999999.9, '0a'],
            ['1b', 999999999.9, '0a'],
            ['1t', 999999999999.9, '0a'],
            ['9999t', 9.999e15, '0a'],
            ['1M', 1000000, '0[.]0A'],
            ['1B', 1000000000, '0[.]0A'],
            ['1T', 1000000000000, '0[.]0A'],
            ['1.4M', 1360000, '0[.]0A'],
            ['1,360K', 1360000, '0,0Ak'],
            ['1.4 M', 1360000, '0[.]0 A'],
            ['1,360 K', 1360000, '0,0 Ak'],
            ['+13.65', 13.652780, '+0.00'],
            ['-13.65', -13.652780, '+0.00'],
            ['13.65', 13.652780, '-0.00'],
            ['-13.65', -13.652780, '-0.00'],
            ['13.65', 13.652780, '(0.00)'],
            ['(13.65)', -13.652780, '(0.00)'],
            ['+13.65', 13.652780, '+-0.00'],
            ['-13.65', -13.652780, '+-0.00'],
            ['.65', 13.652780, '.00'],
            ['14', 13.652780, '0'],
            ['13.7', 13.652780, '1'],
            ['13.65', 13.652780, '2'],
            ['13.653', 13.652780, '3'],
            ['13.6528', 13.652780, '0.00[00]'],
            ['14', 13.652780, '0.[]'],
            ['13.60', 13.6, '0.00[00]'],
            ['0.00', 'thousand', '0,0.00'],
            ['10,000.0000', 10000, '0,0.0000'],
            ['10,000', 10000.23, '0,0'],
            ['+10,000', 10000.23, '+0,0'],
            ['-10,000.0', -10000, '0,0.0'],
            ['10000.123', 10000.1234, '0.000'],
            ['00100', 100.1234, '00000'],
            ['001,000', 1000.1234, '000000,0'],
            ['010.00', 10, '000.00'],
            ['10000.12340', 10000.1234, '0[.]00000'],
            ['(10,000.0000)', -10000, '(0,0.0000)'],
            ['-.23', -0.23, '.00'],
            ['(.23)', -0.23, '(.00)'],
            ['0.23000', 0.23, '0.00000'],
            ['0.23', 0.23, '0.0[0000]'],
            ['1.2m', 1230974, '0.0a'],
            ['1 k', 1460, '0 a'],
            ['-104k', -104000, '0a'],
            ['100B', 100, '0b'],
            ['1KB', 1024, '0b'],
            ['2 KiB', 2048, '0 ib'],
            ['3.1 KB', 3072, '0.0 b'],
            ['7.88GB', 7884486213, '0.00b'],
            ['3.154 TiB', 3467479682787, '0.000 ib'],
            ['13.6528', 13.652780, '0.0000', ['0.0' => false, '0.00' => false]],
            ['13.65', 13.652780, '0.0000', ['0.0' => false, '0.00' => true]],
            ['13.7', 13.652780, '0.0000', ['0.0' => true, '0.00' => false]],
        ];
    }
}
