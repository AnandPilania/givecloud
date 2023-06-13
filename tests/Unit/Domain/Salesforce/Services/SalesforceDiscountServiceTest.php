<?php

namespace Tests\Unit\Domain\Salesforce\Services;

use Ds\Domain\Salesforce\Services\SalesforceDiscountsService;
use Tests\TestCase;

/**
 * @group salesforce
 */
class SalesforceDiscountServiceTest extends TestCase
{
    public function testUpdateLocalReferencesReturnsNullsWhenNotEnabled(): void
    {
        $this->assertNull($this->app->make(SalesforceDiscountsService::class)->updateLocalReferences(collect([])));
    }
}
