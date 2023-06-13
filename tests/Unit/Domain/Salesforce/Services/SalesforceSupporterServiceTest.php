<?php

namespace Tests\Unit\Domain\Salesforce\Services;

use Ds\Domain\Salesforce\Database\Repository;
use Ds\Domain\Salesforce\Models\Supporter;
use Ds\Domain\Salesforce\Services\SalesforceSupporterService;
use Ds\Enums\ExternalReference\ExternalReferenceService;
use Ds\Enums\ExternalReference\ExternalReferenceType;
use Ds\Models\ExternalReference;
use Ds\Models\Member;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Tests\TestCase;

/**
 * @group salesforce
 */
class SalesforceSupporterServiceTest extends TestCase
{
    use WithFaker;

    public function testShouldSyncReporterAlwaysReturnsTrueWhenEnabled(): void
    {
        sys_set('feature_salesforce', true);
        sys_set('salesforce_enabled', true);

        $this->assertTrue($this->app->make(SalesforceSupporterService::class)->shouldSync());
    }

    public function testShouldSyncReporterReturnsFalseWhenNotEnabled(): void
    {
        sys_set('feature_salesforce', true);
        sys_set('salesforce_enabled', false);
        $this->assertFalse($this->app->make(SalesforceSupporterService::class)->shouldSync());
    }

    public function testShouldSyncReporterReturnsFalseWhenFeatureNotEnabled(): void
    {
        sys_set('feature_salesforce', false);
        sys_set('salesforce_enabled', true);
        $this->assertFalse($this->app->make(SalesforceSupporterService::class)->shouldSync());
    }

    public function testUpsertNewSupporterSavesReference(): void
    {
        $uuid = $this->faker->uuid;

        $supporter = new Supporter;
        $supporter->Id = $uuid;

        $member = Member::factory()->individual()->create();

        $mocked = $this->mock(Repository::class);
        $mocked->shouldReceive('firstOrCreate')->andReturn($supporter);
        $mocked->shouldReceive('findByLocalKeys')->andReturn(collect([
            (object) [
                'Givecloud__Givecloud_Supporter_ID__c' => $member->id,
                'Id' => $uuid,
            ],
        ]));

        $salesforceSupporter = $this->app->make(SalesforceSupporterService::class)->upsert($member);

        $this->assertSame($uuid, $salesforceSupporter->Id);

        $this->assertDatabaseHas(ExternalReference::table(), [
            'referenceable_type' => $member->getMorphClass(),
            'referenceable_id' => $member->getKey(),
            'type' => ExternalReferenceType::SUPPORTER,
            'service' => ExternalReferenceService::SALESFORCE,
            'reference' => $uuid,
        ]);
    }

    public function testUpsertMultipleReturnsArrayOfIds(): void
    {
        $members = Member::factory(2)->individual()->create();

        $firstId = $this->faker->uuid;
        $secondId = $this->faker->uuid;

        $repository = $this->partialMock(Repository::class);
        $repository->shouldReceive('upsertRecords')->andReturn([
            ['id' => $firstId],
            ['id' => $secondId],
        ]);

        $repository->shouldReceive('findByLocalKeys')->andReturn(new Collection([
            new Supporter(['Id' => $firstId, 'Givecloud__Givecloud_Supporter_ID__c' => $members[0]->id]),
            new Supporter(['Id' => $secondId, 'Givecloud__Givecloud_Supporter_ID__c' => $members[1]->id]),
        ]));

        $results = $this->app->make(SalesforceSupporterService::class)->upsertMultiple($members);

        $this->assertArrayHasArrayWithValue($results, $firstId, 'id');
        $this->assertArrayHasArrayWithValue($results, $secondId, 'id');

        $this->assertDatabaseHas(ExternalReference::table(), [
            'referenceable_type' => $members->first()->getMorphClass(),
            'referenceable_id' => $members[0]->getKey(),
            'type' => ExternalReferenceType::SUPPORTER,
            'service' => ExternalReferenceService::SALESFORCE,
            'reference' => $firstId,
        ]);

        $this->assertDatabaseHas(ExternalReference::table(), [
            'referenceable_type' => $members->first()->getMorphClass(),
            'referenceable_id' => $members[1]->getKey(),
            'type' => ExternalReferenceType::SUPPORTER,
            'service' => ExternalReferenceService::SALESFORCE,
            'reference' => $secondId,
        ]);
    }

    public function testMapFieldsCanMapMemberFields(): void
    {
        $member = Member::factory()->individual()->create();
        $fields = (new Supporter)->forModel($member)->mapFields();

        $this->assertArrayHasKey('Name', $fields);
        $this->assertArrayHasKey('Givecloud__Supporter_E_mail__c', $fields);
        $this->assertArrayHasKey('Givecloud__Givecloud_Supporter_ID__c', $fields);
        $this->assertArrayNotHasKey('attributes', $fields);

        $this->assertContains($member->display_name, $fields);
        $this->assertContains($member->email, $fields);
        $this->assertContains($member->id, $fields);
    }

    public function testMapFieldsCanMapMemberFieldsWithAttributes(): void
    {
        $member = Member::factory()->individual()->create();
        $fields = (new Supporter)->forModel($member)->mapFields(true);

        $this->assertArrayHasKey('attributes', $fields);
        $this->assertArrayHasKey('type', $fields['attributes']);
        $this->assertArrayHasKey('referenceId', $fields['attributes']);

        $this->assertContains('Givecloud__Supporter__c', $fields['attributes']);
        $this->assertContains($member->getKey(), $fields['attributes']);
    }
}
