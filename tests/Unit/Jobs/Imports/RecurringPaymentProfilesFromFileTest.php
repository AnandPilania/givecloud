<?php

namespace Tests\Unit\Jobs\Imports;

use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Jobs\Import\RecurringPaymentProfilesFromFile;
use Ds\Models\Member;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\InteractsWithImports;
use Tests\Fakes\FakeDonorPerfectConnection;
use Tests\TestCase;

class RecurringPaymentProfilesFromFileTest extends TestCase
{
    use InteractsWithImports;

    public function testGetColumnDefinitions(): void
    {
        $this->assertImportJobColumnDefinitions($this->app->make(RecurringPaymentProfilesFromFile::class));
    }

    public function testAnalyzeRowWithDonorIdToImportFromDP()
    {
        $donorId = random_int(1, 1000);
        Member::factory()->create();
        $tableRow = $this->makeTableRow($donorId);

        $this->mockDPOConnection([
            (object) ['donor_id' => $tableRow['donor_id']],
            (object) ['gift_id' => $tableRow['pledge_id'], 'record_type' => 'P'],
        ]);

        $this->fakeHttpForAnalyzeRow();

        $this->assertSame(
            "Will import Donor ID {$donorId} from DonorPerfect.",
            $this->app->make(RecurringPaymentProfilesFromFile::class)->analyzeRow($tableRow)
        );
    }

    public function testAnalyzeRowWithoutDonorIdOrEmailThrowsException()
    {
        $this->expectExceptionMessage('CANNOT IMPORT. Either an Email or a Donor ID is required.');

        $this->mockDPOConnection();

        $this->app->make(RecurringPaymentProfilesFromFile::class)->analyzeRow($this->makeTableRow());
    }

    public function testAnalyzeRowWithDonorId()
    {
        $donorId = random_int(1, 1000);
        Member::factory()->create(['donor_id' => $donorId]);
        $tableRow = $this->makeTableRow($donorId);

        $this->mockDPOConnection([
            (object) ['donor_id' => $tableRow['donor_id']],
            (object) ['gift_id' => $tableRow['pledge_id'], 'record_type' => 'P'],
        ]);

        $this->fakeHttpForAnalyzeRow();

        $this->assertNull($this->app->make(RecurringPaymentProfilesFromFile::class)->analyzeRow($tableRow));
    }

    protected function makeTableRow(?int $donorId = null): array
    {
        return [
            'donor_id' => $donorId,
            'pledge_id' => random_int(1, 1000),
            'vault_id' => '1234',
        ];
    }

    protected function mockDPOConnection(array $returnResults = []): void
    {
        $this->app->instance('dpo', new FakeDonorPerfectConnection($returnResults));
    }

    protected function fakeHttpForAnalyzeRow(?string $xml = null): void
    {
        PaymentProvider::factory()->nmi()->create();

        $xml = $xml ?: <<<'XML'
            <xml>
                <customer_vault>
                    <customer id="">
                        <customer_vault_id>1234</customer_vault_id>
                        <first_name>Josh</first_name>
                        <last_name>B</last_name>
                        <address_1>address line 1</address_1>
                        <address_2>address line 2</address_2>
                        <company>Givecloud</company>
                        <city>Ottawa</city>
                        <state>ON</state>
                        <postal_code>K2K 0C6</postal_code>
                        <country>Canada</country>
                        <email>josh@givecloud.test</email>
                        <phone>6136782398</phone>
                        <cc_number>1234567890</cc_number>
                        <cc_hash></cc_hash>
                        <cc_exp>0630</cc_exp>
                        <cc_start_date></cc_start_date>
                        <cc_issue_number></cc_issue_number>
                        <check_account></check_account>
                        <check_hash></check_hash>
                        <check_aba></check_aba>
                        <check_name></check_name>
                        <account_holder_type></account_holder_type>
                        <account_type></account_type>
                        <created>2020-11-24 17:12:00</created>
                        <updated>2020-11-24 17:12:00</updated>
                    </customer>
                </customer_vault>
            </xml>
        XML;

        Http::fake(['nmi.com/*' => Http::response($xml)]);
    }
}
