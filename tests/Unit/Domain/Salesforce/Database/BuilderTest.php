<?php

namespace Tests\Unit\Domain\Salesforce\Database;

use Ds\Domain\Salesforce\Database\Builder;
use Ds\Domain\Salesforce\Models\Supporter;
use Omniphx\Forrest\Providers\Laravel\Facades\Forrest;
use Tests\TestCase;

/**
 * @group salesforce
 */
class BuilderTest extends TestCase
{
    public function testUpsertRecordsCallsUnderlyingService(): void
    {
        Forrest::shouldReceive('composite')->andReturn([]);

        $this->app->make(Builder::class)->setModel(new Supporter)->upsertRecords([]);
    }
}
