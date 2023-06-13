<?php

namespace Tests\Feature\Domain\QuickStart\Controllers;

use Ds\Domain\QuickStart\Tasks\DonationItem;
use Ds\Domain\QuickStart\Tasks\TaxReceipts;
use Tests\TestCase;

/** @group QuickStart */
class SkipTaskControllerTest extends TestCase
{
    public function testReturnsNotFoundWhenTaskDoesNotExists()
    {
        $this->actingAsAdminUser()
            ->post(route('backend.quickstart.skip', 'some_task'))
            ->assertNotFound()
            ->assertJsonPath('error', 'Task not found');
    }

    public function testReturnsErrorWhenTaskIsNotSkippable()
    {
        $this->actingAsAdminUser()
            ->post(route('backend.quickstart.skip', DonationItem::initialize()->slug()))
            ->assertStatus(500)
            ->assertJsonPath('error', 'Task is not skippable');
    }

    public function testReturnsSuccessWhenTaskIsSkipped()
    {
        $task = TaxReceipts::initialize();

        $this->assertFalse($task->isSkipped());

        $this->actingAsAdminUser()
            ->post(route('backend.quickstart.skip', $task->slug()))
            ->assertOk()
            ->assertJsonPath('success', 'true');

        $this->assertTrue($task->isSkipped());
    }
}
