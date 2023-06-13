<?php

namespace Tests\Unit\Services;

use Ds\Mail\RppProcessingSummary;
use Ds\Models\RecurringBatch;
use Ds\Models\User;
use Ds\Services\RecurringBatchService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\InteractsWithRpps;
use Tests\TestCase;

class RecurringBatchServiceTest extends TestCase
{
    use InteractsWithRpps;

    public function testStartingRecurringBatch(): void
    {
        $recurringBatch = app(RecurringBatchService::class)->start(0, 1);

        $this->assertInstanceOf(RecurringBatch::class, $recurringBatch);

        $this->assertSame(0, $recurringBatch->accounts_count);
        $this->assertSame(1, $recurringBatch->max_simultaneous);
    }

    public function testAccountProcessed(): void
    {
        $recurringBatch = RecurringBatch::factory()->create([
            'started_at' => now()->subMinutes(20),
        ]);

        app(RecurringBatchService::class)->accountProcessed($recurringBatch);

        $this->assertSame(1, $recurringBatch->refresh()->accounts_processed);
    }

    public function testAccountProcessedOnFinishedRecurringBatch(): void
    {
        $recurringBatch = RecurringBatch::factory()->finished()->create();

        $this->assertFalse(
            app(RecurringBatchService::class)->accountProcessed($recurringBatch)
        );
    }

    public function testFinishingRecurringBatch(): void
    {
        Event::fake();

        $recurringBatch = RecurringBatch::factory()->create([
            'started_at' => now()->subSeconds(1200),
        ]);

        app(RecurringBatchService::class)->finish($recurringBatch);

        $recurringBatch->refresh();

        $this->assertNotNull($recurringBatch->finished_at);
        $this->assertSame(1200, $recurringBatch->elapsed_time);
    }

    public function testFinishingAFinishedRecurringBatch(): void
    {
        $recurringBatch = RecurringBatch::factory()->finished()->create();

        $this->assertFalse(
            app(RecurringBatchService::class)->finish($recurringBatch)
        );
    }

    public function testSendingSummaryToAccountAdmins(): void
    {
        Mail::fake();
        Cache::forget(RppProcessingSummary::class);

        User::factory()->admin()->create(['notify_recurring_batch_summary' => true]);

        app(RecurringBatchService::class)->sendSummaryToAccountAdmins(
            RecurringBatch::factory()->finished()->create()
        );

        Mail::assertSent(RppProcessingSummary::class);
    }

    public function testSendingSummaryToAccountAdminsForAnOngoingRecurringBatch(): void
    {
        Mail::fake();
        Cache::forget(RppProcessingSummary::class);

        User::factory()->admin()->create(['notify_recurring_batch_summary' => true]);

        app(RecurringBatchService::class)->sendSummaryToAccountAdmins(
            RecurringBatch::factory()->create()
        );

        Mail::assertNotSent(RppProcessingSummary::class);
    }
}
