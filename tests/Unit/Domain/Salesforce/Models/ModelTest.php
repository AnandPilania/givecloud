<?php

namespace Tests\Unit\Domain\Salesforce\Models;

use Ds\Domain\Salesforce\Database\Builder;
use Ds\Domain\Salesforce\Models\Supporter;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Tests\TestCase;

/**
 * @group salesforce
 */
class ModelTest extends TestCase
{
    public function testNewEloquentBuilderReturnsSalesforceBuilder(): void
    {
        $this->assertInstanceOf(Builder::class, (new Supporter)->newEloquentBuilder($this->mock(QueryBuilder::class)));
    }
}
