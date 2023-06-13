<?php

namespace Tests\Unit\Mail;

use Ds\Mail\RppProcessingSummary;
use Ds\Models\RecurringBatch;
use Tests\Concerns\InteractsWithMailables;
use Tests\TestCase;

class RppProcessingSummaryTest extends TestCase
{
    use InteractsWithMailables;

    public function testMailablePreview(): void
    {
        $batch = RecurringBatch::factory()->make([
            'accounts_count' => 72,
            'accounts_processed' => 72,
            'transactions_approved' => 64,
            'transactions_declined' => 8,
        ]);

        $mailable = new RppProcessingSummary($batch);

        $this->assertMailablePreview($mailable);
    }
}
