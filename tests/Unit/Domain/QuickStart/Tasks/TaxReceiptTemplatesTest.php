<?php

namespace Tests\Unit\Domain\QuickStart\Tasks;

use Ds\Domain\QuickStart\Tasks\TaxReceipts;
use Ds\Domain\QuickStart\Tasks\TaxReceiptTemplates;
use Ds\Models\TaxReceiptTemplate;
use Tests\TestCase;

/** @group QuickStart */
class TaxReceiptTemplatesTest extends TestCase
{
    public function testDependsOnReturnsArrayOfTaxReceipts(): void
    {
        $dependances = $this->app->make(TaxReceiptTemplates::class)->dependsOn();
        $this->assertSame($dependances[0], TaxReceipts::class);
    }

    public function testIsSkippedReturnsParentSkippedStatus(): void
    {
        $parent = $this->app->make(TaxReceipts::class);
        $task = $this->app->make(TaxReceiptTemplates::class);

        $this->assertFalse($task->isSkipped());
        $this->assertSame($parent->isSkipped(), $task->isSkipped());

        $parent->skip();

        $this->assertTrue($task->isSkipped());
        $this->assertSame($parent->isSkipped(), $task->isSkipped());
    }

    public function testIsCompletedReturnsParentCompletedState(): void
    {
        $parent = $this->app->make(TaxReceipts::class);
        $task = $this->app->make(TaxReceiptTemplates::class);

        $this->assertFalse($task->isCompleted());
        $this->assertSame($parent->isCompleted(), $task->isCompleted());
    }

    public function testIsCompletedLooksForTestStringsAndReturnsStatus(): void
    {
        // Mark completed on parent task;
        sys_set('tax_receipt_pdfs', true);

        $task = $this->app->make(TaxReceiptTemplates::class);

        $this->assertFalse($task->isCompleted());

        $template = TaxReceiptTemplate::query()
            ->where('template_type', 'template')
            ->first();

        $template->body = str_replace('555 Test Address', '555 NotTest Address', $template->body);
        $template->save();

        $this->assertFalse($task->isCompleted());

        $template->body = str_replace('XXXXXXXXXXX', 'Some real content', $template->body);
        $template->save();

        $this->assertTrue($task->isCompleted());
    }
}
