<?php

namespace Tests\Unit\Domain\QuickStart\Tasks;

use Ds\Domain\QuickStart\Tasks\CustomEmails;
use Ds\Models\Email;
use Tests\TestCase;

/** @group QuickStart */
class CustomEmailTest extends TestCase
{
    public function testIsCompleted(): void
    {
        $task = $this->app->make(CustomEmails::class);
        $this->assertFalse($task->isCompleted());

        $this->actingAsAdminUser();

        // Save an email not from user(1).
        Email::query()->active()->first()->save();

        $this->assertTrue($task->isCompleted());
    }
}
