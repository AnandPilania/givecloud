<?php

namespace Tests\Unit\Domain\QuickStart\Tasks;

use Ds\Domain\QuickStart\Tasks\TaxReceipts;
use Tests\TestCase;

/** @group QuickStart */
class TaxReceiptsTest extends TestCase
{
    public function testIsSkippedReturnsSkippedStatus(): void
    {
        $task = $this->app->make(TaxReceipts::class);
        $this->assertFalse($task->isSkipped());

        $task->skip();

        $this->assertTrue($task->isSkipped());

        $task->unskip();

        $this->assertFalse($task->isSkipped());
    }

    public function testIsCompletedReturnsCompletedStatus(): void
    {
        $task = $this->app->make(TaxReceipts::class);
        $task->skip();

        $this->assertTrue($task->isCompleted());

        $task->unskip();

        $this->assertFalse($task->isCompleted());

        sys_set('tax_receipt_pdfs', true);

        $this->assertTrue($task->isCompleted());
    }
}
