<?php

namespace Tests\Unit\Models;

use Ds\Models\TaxReceipt;
use Tests\StoryBuilder;
use Tests\TestCase;

class TaxReceiptTest extends TestCase
{
    /**
     * @dataProvider supporterCountryValuesWhenCreatingTaxReceiptFromContributionProvider
     */
    public function testSupporterCountryValuesWhenCreatingTaxReceiptFromContribution(?string $contributionCountry, ?string $supporterCountry, bool $expectsException): void
    {
        $contribution = StoryBuilder::onetimeContribution()->create();

        $contribution->billingcountry = $contributionCountry;
        $contribution->save();

        $contribution->member->bill_country = $supporterCountry;
        $contribution->member->save();

        if ($expectsException) {
            $this->expectExceptionMessageMatches('/Receipts can only be issued on US contributions/');
        }

        sys_set('tax_receipt_pdfs', true);

        $taxReceipt = TaxReceipt::createFromOrder($contribution->getKey());

        $this->assertInstanceOf(TaxReceipt::class, $taxReceipt);
    }

    public function supporterCountryValuesWhenCreatingTaxReceiptFromContributionProvider(): array
    {
        return [
            ['US', null, false],
            [null, 'US', false],
            [null, null, true],
        ];
    }

    /**
     * @dataProvider supporterCountryValuesWhenCreatingTaxReceiptFromTransactionProvider
     */
    public function testSupporterCountryValuesWhenCreatingTaxReceiptFromTransaction(?string $contributionCountry, ?string $supporterCountry, bool $expectsException): void
    {
        $rpp = StoryBuilder::recurringContribution()
            ->includingPayments(1)
            ->create();

        $rpp->order->billingcountry = $contributionCountry;
        $rpp->order->save();

        $rpp->order->member->bill_country = $supporterCountry;
        $rpp->order->member->save();

        if ($expectsException) {
            $this->expectExceptionMessageMatches('/Receipts can only be issued in US/');
        }

        sys_set('tax_receipt_pdfs', true);

        $taxReceipt = TaxReceipt::createFromTransaction($rpp->last_transaction->getKey());

        $this->assertInstanceOf(TaxReceipt::class, $taxReceipt);
    }

    public function supporterCountryValuesWhenCreatingTaxReceiptFromTransactionProvider(): array
    {
        return [
            ['US', null, false],
            [null, 'US', false],
            [null, null, true],
        ];
    }
}
