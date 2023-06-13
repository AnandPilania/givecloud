<?php

namespace Tests\Feature\Domain\QuickStart\Controllers;

use Ds\Domain\QuickStart\Tasks\DonationItem;
use Ds\Domain\QuickStart\Tasks\TaxReceipts;
use Tests\TestCase;

/** @group QuickStart */
class UnSkipTaskControllerTest extends TestCase
{
    public function testReturnsNotFoundWhenTaskDoesNotExists()
    {
        $this->actingAsAdminUser()
            ->delete(route('backend.quickstart.unskip', 'some_task'))
            ->assertNotFound()
            ->assertJsonPath('error', 'Task not found');
    }

    public function testReturnsErrorWhenTaskIsNotSkippable()
    {
        $this->actingAsAdminUser()
            ->delete(route('backend.quickstart.unskip', DonationItem::initialize()->slug()))
            ->assertStatus(500)
            ->assertJsonPath('error', 'Task is not skippable');
    }

    public function testReturnsSuccessWhenTaskIsUnSkipped()
    {
        /** @var \Ds\Domain\QuickStart\Tasks\TaxReceipts $task */
        $task = TaxReceipts::initialize();
        $task->skip();

        $this->assertTrue($task->isSkipped());

        $this->actingAsAdminUser()
            ->delete(route('backend.quickstart.unskip', $task->slug()))
            ->assertOk()
            ->assertJsonPath('success', 'true');

        $this->assertFalse($task->isSkipped());
    }
}
