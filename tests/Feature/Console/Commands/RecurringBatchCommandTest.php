<?php

namespace Tests\Feature\Console\Commands;

use Ds\Events\RecurringBatchCompleted;
use Ds\Mail\RppProcessingSummary;
use Ds\Models\RecurringBatch;
use Ds\Models\User;
use Ds\Repositories\RecurringBatchRepository;
use Ds\Services\RecurringBatchService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\Concerns\InteractsWithRpps;
use Tests\TestCase;

/**
 * @group backend
 * @group console
 * @group rpp
 */
class RecurringBatchCommandTest extends TestCase
{
    use InteractsWithRpps;

    public function testChargeableRpps()
    {
        Mail::fake();
        Event::fake([RecurringBatchCompleted::class]);
        Cache::forget(RppProcessingSummary::class);

        User::factory()->admin()->create(['notify_recurring_batch_summary' => true]);

        $batch = RecurringBatch::factory()->create(['accounts_count' => 2]);

        $this->instance(
            RecurringBatchService::class,
            Mockery::mock(RecurringBatchService::class, [app(RecurringBatchRepository::class)], function ($mock) use ($batch) {
                $mock->shouldReceive('start')->once()->andReturn($batch);
            })->makePartial()
        );

        $this->generateAccountsWithPMsAndRpps(3);
        $this->generateAccountsWithPMsHavingInsuffientFundsAndRpps(2);

        $this->artisan('recurring:batch')->assertExitCode(0);

        $batch->refresh();

        $this->assertSame(5, $batch->accounts_processed);
        $this->assertSame(3, $batch->transactions_approved);
        $this->assertSame(2, $batch->transactions_declined);

        Mail::assertSent(RppProcessingSummary::class);
    }

    public function testChargeableRppsDryRun()
    {
        $accounts = $this->generateAccountsWithPMsAndRpps(2);

        $this->artisan('recurring:batch', ['--dry-run' => true])
            ->expectsOutput(sprintf(
                'Process (%d) supporters with chargeable profiles',
                count($accounts)
            ))->assertExitCode(0);
    }

    public function testParallelChargeableRppsDryRun()
    {
        $accounts = $this->generateAccountsWithPMsAndRpps(2);

        $this->artisan('recurring:batch', ['--dry-run' => true, '--threshold' => 0])
            ->expectsOutput(sprintf(
                'Process (%d) supporters with chargeable profiles',
                count($accounts)
            ))->assertExitCode(0);
    }
}
