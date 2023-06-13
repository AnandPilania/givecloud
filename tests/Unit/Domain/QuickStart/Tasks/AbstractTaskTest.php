<?php

namespace Tests\Unit\Domain\QuickStart\Tasks;

use Ds\Domain\QuickStart\QuickStartService;
use Ds\Domain\QuickStart\Tasks\TaxReceipts;
use Tests\TestCase;

/** @group QuickStart */
class AbstractTaskTest extends TestCase
{
    public function testUpdateCallsService(): void
    {
        $this->mock(QuickStartService::class)->shouldReceive('updateTaskStatus')->once();
        TaxReceipts::initialize()->update();
    }

    public function testJsonSerializeReturnsArray(): void
    {
        $task = TaxReceipts::initialize();
        $this->assertSame($task->jsonSerialize(), $task->toArray());
    }

    public function testToJsonReturnsJsonRepresentation(): void
    {
        $task = TaxReceipts::initialize();
        $json = $task->toJson();

        $this->assertJson($json);

        $this->assertSame(json_decode($json, true), $task->toArray());
    }
}
