<?php

namespace Tests\Unit\Domain\QuickStart\Tasks;

use Ds\Domain\QuickStart\Tasks\TestTransactions;
use Ds\Models\Payment;
use Tests\TestCase;

/** @group QuickStart */
class TasksTestTransactionTest extends TestCase
{
    public function testIsCompletedIsFalseWhenNoPayments(): void
    {
        $task = $this->app->make(TestTransactions::class);

        $this->assertFalse($task->isCompleted());

        Payment::factory()->create([
            'livemode' => true,
        ]);

        $this->assertFalse($task->isCompleted());
    }

    public function testIsCompletedIsTrueWhenATestPaymentExists(): void
    {
        $task = $this->app->make(TestTransactions::class);

        $this->assertFalse($task->isCompleted());

        Payment::factory()->create();

        $this->assertTrue($task->isCompleted());
    }
}
